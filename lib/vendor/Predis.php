<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Predis\Commands;

use Predis\Helpers;
use Predis\Distribution\INodeKeyGenerator;
use Predis\Iterators\MultiBulkResponseTuple;

/**
 * Defines an abstraction representing a Redis command.
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface ICommand
{
    /**
     * Gets the ID of a Redis command.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns an hash of the command using the provided algorithm against the
     * key (used to calculate the distribution of keys with client-side sharding).
     *
     * @param INodeKeyGenerator $distributor Distribution algorithm.
     * @return int
     */
    public function getHash(INodeKeyGenerator $distributor);

    /**
     * Sets the arguments of the command.
     *
     * @param array $arguments List of arguments.
     */
    public function setArguments(Array $arguments);

    /**
     * Gets the arguments of the command.
     *
     * @return array
     */
    public function getArguments();

    /**
     * Prefixes all the keys in the arguments of the command.
     *
     * @param string $prefix String user to prefix the keys.
     */
    public function prefixKeys($prefix);

    /**
     * Parses a reply buffer and returns a PHP object.
     *
     * @param string $data Binary string containing the whole reply.
     * @return mixed
     */
    public function parseResponse($data);
}

/**
 * Base class for Redis commands.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
abstract class Command implements ICommand
{
    private $_hash;
    private $_arguments = array();

    /**
     * Returns a filtered array of the arguments.
     *
     * @param array $arguments List of arguments.
     * @return array
     */
    protected function filterArguments(Array $arguments)
    {
        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function setArguments(Array $arguments)
    {
        $this->_arguments = $this->filterArguments($arguments);
        unset($this->_hash);
    }

    /**
     * Sets the arguments array without filtering.
     *
     * @param array $arguments List of arguments.
     */
    public function setRawArguments(Array $arguments)
    {
        $this->_arguments = $arguments;
        unset($this->_hash);
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * Gets the argument from the arguments list at the specified index.
     *
     * @param array $arguments Position of the argument.
     */
    public function getArgument($index = 0)
    {
        if (isset($this->_arguments[$index]) === true) {
            return $this->_arguments[$index];
        }
    }

    /**
     * Implements the rule that is used to prefix the keys and returns a new
     * array of arguments with the modified keys.
     *
     * @param array $arguments Arguments of the command.
     * @param string $prefix Prefix appended to each key in the arguments.
     * @return array
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        $arguments[0] = "$prefix{$arguments[0]}";
        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function prefixKeys($prefix)
    {
        $arguments = $this->onPrefixKeys($this->_arguments, $prefix);
        if (isset($arguments)) {
            $this->_arguments = $arguments;
            unset($this->_hash);
        }
    }

    /**
     * Checks if the command can return an hash for client-side sharding.
     *
     * @return Boolean
     */
    protected function canBeHashed()
    {
        return isset($this->_arguments[0]);
    }

    /**
     * Checks if the specified array of keys will generate the same hash.
     *
     * @param array $keys Array of keys.
     * @return Boolean
     */
    protected function checkSameHashForKeys(Array $keys)
    {
        if (($count = count($keys)) === 0) {
            return false;
        }

        $currentKey = Helpers::getKeyHashablePart($keys[0]);

        for ($i = 1; $i < $count; $i++) {
            $nextKey = Helpers::getKeyHashablePart($keys[$i]);
            if ($currentKey !== $nextKey) {
                return false;
            }
            $currentKey = $nextKey;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getHash(INodeKeyGenerator $distributor)
    {
        if (isset($this->_hash)) {
            return $this->_hash;
        }

        if ($this->canBeHashed()) {
            $key = Helpers::getKeyHashablePart($this->_arguments[0]);
            $this->_hash = $distributor->generateKey($key);

            return $this->_hash;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return $data;
    }

    /**
     * Helper function used to reduce a list of arguments to a string.
     *
     * @param string $accumulator Temporary string.
     * @param string $argument Current argument.
     * @return string
     */
    protected function toStringArgumentReducer($accumulator, $argument)
    {
        if (strlen($argument) > 32) {
            $argument = substr($argument, 0, 32) . '[...]';
        }
        $accumulator .= " $argument";

        return $accumulator;
    }

    /**
     * Returns a partial string representation of the command with its arguments.
     *
     * @return string
     */
    public function __toString()
    {
        return array_reduce(
            $this->getArguments(),
            array($this, 'toStringArgumentReducer'),
            $this->getId()
        );
    }
}

/**
 * @link http://redis.io/commands/zrange
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetRange extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZRANGE';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        if (count($arguments) === 4) {
            $lastType = gettype($arguments[3]);

            if ($lastType === 'string' && strtolower($arguments[3]) === 'withscores') {
                // Used for compatibility with older versions
                $arguments[3] = array('WITHSCORES' => true);
                $lastType = 'array';
            }

            if ($lastType === 'array') {
                $options = $this->prepareOptions(array_pop($arguments));
                return array_merge($arguments, $options);
            }
        }

        return $arguments;
    }

    /**
     * Returns a list of options and modifiers compatible with Redis.
     *
     * @param array $options List of options.
     * @return array
     */
    protected function prepareOptions($options)
    {
        $opts = array_change_key_case($options, CASE_UPPER);
        $finalizedOpts = array();

        if (isset($opts['WITHSCORES'])) {
            $finalizedOpts[] = 'WITHSCORES';
        }

        return $finalizedOpts;
    }

    /**
     * Checks for the presence of the WITHSCORES modifier.
     *
     * @return Boolean
     */
    protected function withScores()
    {
        $arguments = $this->getArguments();

        if (count($arguments) < 4) {
            return false;
        }

        return strtoupper($arguments[3]) === 'WITHSCORES';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        if ($this->withScores()) {
            if ($data instanceof \Iterator) {
                return new MultiBulkResponseTuple($data);
            }

            $result = array();

            for ($i = 0; $i < count($data); $i++) {
                $result[] = array($data[$i], $data[++$i]);
            }

            return $result;
        }

        return $data;
    }
}

/**
 * @link http://redis.io/commands/sinterstore
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetIntersectionStore extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SINTERSTORE';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        if (count($arguments) === 2 && is_array($arguments[1])) {
            return array_merge(array($arguments[0]), $arguments[1]);
        }

        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        return PrefixHelpers::multipleKeys($arguments, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return $this->checkSameHashForKeys($this->getArguments());
    }
}

/**
 * @link http://redis.io/commands/eval
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerEval extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'EVAL';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        $arguments = $this->getArguments();

        for ($i = 2; $i < $arguments[1] + 2; $i++) {
            $arguments[$i] = "$prefix{$arguments[$i]}";
        }

        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/sinter
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetIntersection extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SINTER';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        return Helpers::filterArrayArguments($arguments);
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        return PrefixHelpers::multipleKeys($arguments, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return $this->checkSameHashForKeys($this->getArguments());
    }
}

/**
 * @link http://redis.io/commands/zunionstore
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetUnionStore extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZUNIONSTORE';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        $options = array();
        $argc = count($arguments);

        if ($argc > 2 && is_array($arguments[$argc - 1])) {
            $options = $this->prepareOptions(array_pop($arguments));
        }

        if (is_array($arguments[1])) {
            $arguments = array_merge(
                array($arguments[0], count($arguments[1])),
                $arguments[1]
            );
        }

        return array_merge($arguments, $options);
    }

    /**
     * Returns a list of options and modifiers compatible with Redis.
     *
     * @param array $options List of options.
     * @return array
     */
    private function prepareOptions($options)
    {
        $opts = array_change_key_case($options, CASE_UPPER);
        $finalizedOpts = array();

        if (isset($opts['WEIGHTS']) && is_array($opts['WEIGHTS'])) {
            $finalizedOpts[] = 'WEIGHTS';
            foreach ($opts['WEIGHTS'] as $weight) {
                $finalizedOpts[] = $weight;
            }
        }

        if (isset($opts['AGGREGATE'])) {
            $finalizedOpts[] = 'AGGREGATE';
            $finalizedOpts[] = $opts['AGGREGATE'];
        }

        return $finalizedOpts;
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        $arguments[0] = "$prefix{$arguments[0]}";
        $length = ((int) $arguments[1]) + 2;

        for ($i = 2; $i < $length; $i++) {
            $arguments[$i] = "$prefix{$arguments[$i]}";
        }

        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        $args = $this->getArguments();

        return $this->checkSameHashForKeys(
            array_merge(array($args[0]), array_slice($args, 2, $args[1]))
        );
    }
}

/**
 * @link http://redis.io/commands/subscribe
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class PubSubSubscribe extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SUBSCRIBE';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        return Helpers::filterArrayArguments($arguments);
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        return PrefixHelpers::multipleKeys($arguments, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/keys
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyKeys extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'KEYS';
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/blpop
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListPopFirstBlocking extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'BLPOP';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        return PrefixHelpers::skipLastArgument($arguments, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return $this->checkSameHashForKeys(
            array_slice(($args = $this->getArguments()), 0, count($args) - 1)
        );
    }
}

/**
 * @link http://redis.io/commands/rpush
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListPushTail extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'RPUSH';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        return Helpers::filterVariadicValues($arguments);
    }
}

/**
 * @link http://redis.io/commands/mset
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringSetMultiple extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'MSET';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        if (count($arguments) === 1 && is_array($arguments[0])) {
            $flattenedKVs = array();
            $args = $arguments[0];

            foreach ($args as $k => $v) {
                $flattenedKVs[] = $k;
                $flattenedKVs[] = $v;
            }

            return $flattenedKVs;
        }

        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        $length = count($arguments);

        for ($i = 0; $i < $length; $i += 2) {
            $arguments[$i] = "$prefix{$arguments[$i]}";
        }

        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        $args = $this->getArguments();
        $keys = array();

        for ($i = 0; $i < count($args); $i += 2) {
            $keys[] = $args[$i];
        }

        return $this->checkSameHashForKeys($keys);
    }
}

/**
 * @link http://redis.io/commands/rename
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyRename extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'RENAME';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        return PrefixHelpers::multipleKeys($arguments, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/unsubscribe
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class PubSubUnsubscribe extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'UNSUBSCRIBE';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        return PrefixHelpers::multipleKeys($arguments, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/info
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerInfo extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'INFO';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        $info      = array();
        $infoLines = explode("\r\n", $data, -1);

        foreach ($infoLines as $row) {
            @list($k, $v) = explode(':', $row);

            if ($row === '' || !isset($v)) {
                continue;
            }

            if (!preg_match('/^db\d+$/', $k)) {
                if ($k === 'allocation_stats') {
                    $info[$k] = $this->parseAllocationStats($v);
                    continue;
                }
                $info[$k] = $v;
            }
            else {
                $info[$k] = $this->parseDatabaseStats($v);
            }
        }

        return $info;
    }

    /**
     * Parses the reply buffer and extracts the statistics of each logical DB.
     *
     * @param string $str Reply buffer.
     * @return array
     */
    protected function parseDatabaseStats($str)
    {
        $db = array();

        foreach (explode(',', $str) as $dbvar) {
            list($dbvk, $dbvv) = explode('=', $dbvar);
            $db[trim($dbvk)] = $dbvv;
        }

        return $db;
    }

    /**
     * Parses the reply buffer and extracts the allocation statistics.
     *
     * @param string $str Reply buffer.
     * @return array
     */
    protected function parseAllocationStats($str)
    {
        $stats = array();

        foreach (explode(',', $str) as $kv) {
            @list($size, $objects, $extra) = explode('=', $kv);

            // hack to prevent incorrect values when parsing the >=256 key
            if (isset($extra)) {
                $size = ">=$objects";
                $objects = $extra;
            }
            $stats[$size] = $objects;
        }

        return $stats;
    }
}

/**
 * @link http://redis.io/commands/zrangebyscore
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetRangeByScore extends ZSetRange
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZRANGEBYSCORE';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOptions($options)
    {
        $opts = array_change_key_case($options, CASE_UPPER);
        $finalizedOpts = array();

        if (isset($opts['LIMIT']) && is_array($opts['LIMIT'])) {
            $limit = array_change_key_case($opts['LIMIT'], CASE_UPPER);

            $finalizedOpts[] = 'LIMIT';
            $finalizedOpts[] = isset($limit['OFFSET']) ? $limit['OFFSET'] : $limit[0];
            $finalizedOpts[] = isset($limit['COUNT']) ? $limit['COUNT'] : $limit[1];
        }

        return array_merge($finalizedOpts, parent::prepareOptions($options));
    }

    /**
     * {@inheritdoc}
     */
    protected function withScores()
    {
        $arguments = $this->getArguments();

        for ($i = 3; $i < count($arguments); $i++) {
            switch (strtoupper($arguments[$i])) {
                case 'WITHSCORES':
                    return true;

                case 'LIMIT':
                    $i += 2;
                    break;
            }
        }

        return false;
    }
}

/**
 * @link http://redis.io/commands/append
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringAppend extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'APPEND';
    }
}

/**
 * @link http://redis.io/commands/object
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerObject extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'OBJECT';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/sunionstore
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetUnionStore extends SetIntersectionStore
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SUNIONSTORE';
    }
}

/**
 * @link http://redis.io/commands/shutdown
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerShutdown extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId() {
        return 'SHUTDOWN';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/save
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerSave extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SAVE';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/decr
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringDecrement extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'DECR';
    }
}

/**
 * @link http://redis.io/commands/get
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringGet extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'GET';
    }
}

/**
 * @link http://redis.io/commands/decrby
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringDecrementBy extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'DECRBY';
    }
}

/**
 * @link http://redis.io/commands/sunion
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetUnion extends SetIntersection
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SUNION';
    }
}

/**
 * @link http://redis.io/commands/getbit
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringGetBit extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'GETBIT';
    }
}

/**
 * @link http://redis.io/commands/srandmember
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetRandomMember extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SRANDMEMBER';
    }
}

/**
 * @link http://redis.io/commands/mget
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringGetMultiple extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'MGET';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        return Helpers::filterArrayArguments($arguments);
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        return PrefixHelpers::multipleKeys($arguments, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return $this->checkSameHashForKeys($this->getArguments());
    }
}

/**
 * @link http://redis.io/commands/sismember
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetIsMember extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SISMEMBER';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/scard
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetCardinality extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SCARD';
    }
}

/**
 * @link http://redis.io/commands/sdiff
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetDifference extends SetIntersection
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SDIFF';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        return PrefixHelpers::multipleKeys($arguments, $prefix);
    }
}

/**
 * @link http://redis.io/commands/smembers
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetMembers extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SMEMBERS';
    }
}

/**
 * @link http://redis.io/commands/sadd
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetAdd extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SADD';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        return Helpers::filterVariadicValues($arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/sdiffstore
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetDifferenceStore extends SetIntersectionStore
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SDIFFSTORE';
    }
}

/**
 * @link http://redis.io/commands/spop
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetPop  extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SPOP';
    }
}

/**
 * @link http://redis.io/commands/slaveof
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerSlaveOf extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SLAVEOF';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        if (count($arguments) === 0 || $arguments[0] === 'NO ONE') {
            return array('NO', 'ONE');
        }

        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/smove
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetMove extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SMOVE';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        return PrefixHelpers::skipLastArgument($arguments, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/srem
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SetRemove extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SREM';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        return Helpers::filterVariadicValues($arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/incrby
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringIncrementBy extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'INCRBY';
    }
}

/**
 * @link http://redis.io/commands/zincrby
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetIncrementBy extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZINCRBY';
    }
}

/**
 * @link http://redis.io/commands/zinterstore
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetIntersectionStore extends ZSetUnionStore
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZINTERSTORE';
    }
}

/**
 * @link http://redis.io/commands/zcount
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetCount extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZCOUNT';
    }
}

/**
 * @link http://redis.io/commands/zcard
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetCardinality extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZCARD';
    }
}

/**
 * @link http://redis.io/commands/watch
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class TransactionWatch extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'WATCH';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        if (isset($arguments[0]) && is_array($arguments[0])) {
            return $arguments[0];
        }

        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        return PrefixHelpers::multipleKeys($arguments, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/zadd
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetAdd extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZADD';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        return Helpers::filterVariadicValues($arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/zrank
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetRank extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZRANK';
    }
}

/**
 * @link http://redis.io/commands/zrem
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetRemove extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZREM';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        return Helpers::filterVariadicValues($arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/zrevrank
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetReverseRank extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZREVRANK';
    }
}

/**
 * @link http://redis.io/commands/zscore
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetScore extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZSCORE';
    }
}

/**
 * @link http://redis.io/commands/zrevrangebyscore
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetReverseRangeByScore extends ZSetRangeByScore
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZREVRANGEBYSCORE';
    }
}

/**
 * @link http://redis.io/commands/zrevrange
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetReverseRange extends ZSetRange
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZREVRANGE';
    }
}

/**
 * @link http://redis.io/commands/zremrangebyrank
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetRemoveRangeByRank extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZREMRANGEBYRANK';
    }
}

/**
 * @link http://redis.io/commands/zremrangebyscore
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetRemoveRangeByScore extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZREMRANGEBYSCORE';
    }
}

/**
 * @link http://redis.io/commands/unwatch
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class TransactionUnwatch extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'UNWATCH';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/multi
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class TransactionMulti extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'MULTI';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/setbit
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringSetBit extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SETBIT';
    }
}

/**
 * @link http://redis.io/commands/setex
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringSetExpire extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SETEX';
    }
}

/**
 * @link http://redis.io/commands/set
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringSet extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SET';
    }
}

/**
 * @link http://redis.io/commands/monitor
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerMonitor extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'MONITOR';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/getset
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringGetSet extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'GETSET';
    }
}

/**
 * @link http://redis.io/commands/incr
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringIncrement extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'INCR';
    }
}

/**
 * @link http://redis.io/commands/msetnx
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringSetMultiplePreserve extends StringSetMultiple
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'MSETNX';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/setnx
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringSetPreserve extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SETNX';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/discard
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class TransactionDiscard extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'DISCARD';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/exec
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class TransactionExec extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'EXEC';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/substr
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringSubstr extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SUBSTR';
    }
}

/**
 * @link http://redis.io/commands/strlen
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringStrlen extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'STRLEN';
    }
}

/**
 * @link http://redis.io/commands/setrange
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringSetRange extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SETRANGE';
    }
}

/**
 * @link http://redis.io/commands/getrange
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StringGetRange extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'GETRANGE';
    }
}

/**
 * @link http://redis.io/commands/evalsha
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerEvalSHA extends ServerEval
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'EVALSHA';
    }
}

/**
 * @link http://redis.io/commands/expire
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyExpire extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'EXPIRE';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/expireat
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyExpireAt extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'EXPIREAT';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/exists
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyExists extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'EXISTS';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/del
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyDelete extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'DEL';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        return Helpers::filterArrayArguments($arguments);
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        return PrefixHelpers::multipleKeys($arguments, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        $args = $this->getArguments();
        if (count($args) === 1) {
            return true;
        }

        return $this->checkSameHashForKeys($args);
    }
}

/**
 * @link http://redis.io/commands/hsetnx
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class HashSetPreserve extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'HSETNX';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/hvals
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class HashValues extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'HVALS';
    }
}

/**
 * @link http://redis.io/commands/keys
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyKeysV12x extends KeyKeys
{
    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return explode(' ', $data);
    }
}

/**
 * @link http://redis.io/commands/move
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyMove extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'MOVE';
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/sort
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeySort extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SORT';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        if (count($arguments) === 1) {
            return $arguments;
        }

        $query = array($arguments[0]);
        $sortParams = array_change_key_case($arguments[1], CASE_UPPER);

        if (isset($sortParams['BY'])) {
            $query[] = 'BY';
            $query[] = $sortParams['BY'];
        }

        if (isset($sortParams['GET'])) {
            $getargs = $sortParams['GET'];
            if (is_array($getargs)) {
                foreach ($getargs as $getarg) {
                    $query[] = 'GET';
                    $query[] = $getarg;
                }
            }
            else {
                $query[] = 'GET';
                $query[] = $getargs;
            }
        }

        if (isset($sortParams['LIMIT']) && is_array($sortParams['LIMIT'])
            && count($sortParams['LIMIT']) == 2) {

            $query[] = 'LIMIT';
            $query[] = $sortParams['LIMIT'][0];
            $query[] = $sortParams['LIMIT'][1];
        }

        if (isset($sortParams['SORT'])) {
            $query[] = strtoupper($sortParams['SORT']);
        }

        if (isset($sortParams['ALPHA']) && $sortParams['ALPHA'] == true) {
            $query[] = 'ALPHA';
        }

        if (isset($sortParams['STORE'])) {
            $query[] = 'STORE';
            $query[] = $sortParams['STORE'];
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        $arguments[0] = "$prefix{$arguments[0]}";

        if (($count = count($arguments)) > 1) {
            for ($i = 1; $i < $count; $i++) {
                switch ($arguments[$i]) {
                    case 'BY':
                    case 'STORE':
                        $arguments[$i] = "$prefix{$arguments[++$i]}";
                        break;

                    case 'GET':
                        $value = $arguments[++$i];
                        if ($value !== '#') {
                            $arguments[$i] = "$prefix$value";
                        }
                        break;

                    case 'LIMIT';
                        $i += 2;
                        break;
                }
            }
        }

        return $arguments;
    }
}

/**
 * @link http://redis.io/commands/ttl
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyTimeToLive extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'TTL';
    }
}

/**
 * @link http://redis.io/commands/renamenx
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyRenamePreserve extends KeyRename
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'RENAMENX';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/randomkey
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyRandom extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'RANDOMKEY';
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return $data !== '' ? $data : null;
    }
}

/**
 * @link http://redis.io/commands/persist
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyPersist extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'PERSIST';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/hmset
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class HashSetMultiple extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'HMSET';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        if (count($arguments) === 2 && is_array($arguments[1])) {
            $flattenedKVs = array($arguments[0]);
            $args = $arguments[1];

            foreach ($args as $k => $v) {
                $flattenedKVs[] = $k;
                $flattenedKVs[] = $v;
            }

            return $flattenedKVs;
        }

        return $arguments;
    }
}

/**
 * @link http://redis.io/commands/hset
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class HashSet extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'HSET';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/select
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ConnectionSelect extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'SELECT';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/hdel
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class HashDelete extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'HDEL';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        return Helpers::filterVariadicValues($arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/quit
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ConnectionQuit extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'QUIT';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/ping
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ConnectionPing extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'PING';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return $data === 'PONG' ? true : false;
    }
}

/**
 * @link http://redis.io/commands/auth
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ConnectionAuth extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'AUTH';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/echo
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ConnectionEcho extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ECHO';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/hexists
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class HashExists extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'HEXISTS';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return (bool) $data;
    }
}

/**
 * @link http://redis.io/commands/hget
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class HashGet extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'HGET';
    }
}

/**
 * @link http://redis.io/commands/hkeys
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class HashKeys extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'HKEYS';
    }
}

/**
 * @link http://redis.io/commands/hlen
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class HashLength extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'HLEN';
    }
}

/**
 * @link http://redis.io/commands/hincrby
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class HashIncrementBy extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'HINCRBY';
    }
}

/**
 * @link http://redis.io/commands/hmget
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class HashGetMultiple extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'HMGET';
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        return Helpers::filterVariadicValues($arguments);
    }
}

/**
 * @link http://redis.io/commands/hgetall
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class HashGetAll extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'HGETALL';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        if ($data instanceof \Iterator) {
            return new MultiBulkResponseTuple($data);
        }

        $result = array();
        for ($i = 0; $i < count($data); $i++) {
            $result[$data[$i]] = $data[++$i];
        }

        return $result;
    }
}

/**
 * @link http://redis.io/commands/type
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyType extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'TYPE';
    }
}

/**
 * @link http://redis.io/commands/lindex
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListIndex extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'LINDEX';
    }
}

/**
 * Base class used to implement an higher level abstraction for "virtual"
 * commands based on EVAL.
 *
 * @link http://redis.io/commands/eval
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
abstract class ScriptedCommand extends ServerEval
{
    /**
     * Gets the body of a Lua script.
     *
     * @return string
     */
    public abstract function getScript();

    /*
     * Gets the number of arguments that should be considered as keys.
     *
     * @return int
     */
    protected function keysCount()
    {
        // The default behaviour for the base class is to use all the arguments
        // passed to a scripted command to populate the KEYS table in Lua.
        return count($this->getArguments());
    }

    /**
     * {@inheritdoc}
     */
    protected function filterArguments(Array $arguments)
    {
        return array_merge(array($this->getScript(), $this->keysCount()), $arguments);
    }

    /**
     * {@inheritdoc}
     */
    protected function getKeys()
    {
        return array_slice($this->getArguments(), 2, $this->keysCount());
    }
}

/**
 * @link http://redis.io/commands/bgrewriteaof
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerBackgroundRewriteAOF extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'BGREWRITEAOF';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return $data == 'Background append only file rewriting started';
    }
}

/**
 * @link http://redis.io/commands/punsubscribe
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class PubSubUnsubscribeByPattern extends PubSubUnsubscribe
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'PUNSUBSCRIBE';
    }
}

/**
 * @link http://redis.io/commands/psubscribe
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class PubSubSubscribeByPattern extends PubSubSubscribe
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'PSUBSCRIBE';
    }
}

/**
 * Class that defines a few helpers method for prefixing keys.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class PrefixHelpers
{
    /**
     * Applies the specified prefix to all the arguments.
     *
     * @param array $arguments Array of arguments.
     * @param string $prefix The prefix string.
     * @return array
     */
    public static function multipleKeys(Array $arguments, $prefix)
    {
        foreach ($arguments as &$key) {
            $key = "$prefix$key";
        }

        return $arguments;
    }

    /**
     * Applies the specified prefix to all the arguments but the last one.
     *
     * @param array $arguments Array of arguments.
     * @param string $prefix The prefix string.
     * @return array
     */
    public static function skipLastArgument(Array $arguments, $prefix)
    {
        $length = count($arguments);
        for ($i = 0; $i < $length - 1; $i++) {
            $arguments[$i] = "$prefix{$arguments[$i]}";
        }

        return $arguments;
    }
}

/**
 * @link http://redis.io/commands/publish
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class PubSubPublish extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'PUBLISH';
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/bgsave
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerBackgroundSave extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'BGSAVE';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        if ($data == 'Background saving started') {
            return true;
        }

        return $data;
    }
}

/**
 * @link http://redis.io/commands/client
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerClient extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'CLIENT';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        $args = array_change_key_case($this->getArguments(), CASE_UPPER);
        switch (strtoupper($args[0])) {
            case 'LIST':
                return $this->parseClientList($data);

            case 'KILL':
            default:
                return $data;
        }
    }

    /**
     * Parses the reply buffer and returns the list of clients returned by
     * the CLIENT LIST command.
     *
     * @param string $data Reply buffer
     * @return array
     */
    protected function parseClientList($data)
    {
        $clients = array();

        foreach (explode("\n", $data, -1) as $clientData) {
            $client = array();
            foreach (explode(' ', $clientData) as $kv) {
                @list($k, $v) = explode('=', $kv);
                $client[$k] = $v;
            }
            $clients[] = $client;
        }

        return $clients;
    }
}

/**
 * @link http://redis.io/commands/flushdb
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerFlushDatabase extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'FLUSHDB';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/info
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerInfoV26x extends ServerInfo
{
    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        $info = array();
        $current = null;
        $infoLines = explode("\r\n", $data, -1);

        foreach ($infoLines as $row) {
            if ($row === '') {
                continue;
            }

            if (preg_match('/^# (\w+)$/', $row, $matches)) {
                $info[$matches[1]] = array();
                $current = &$info[$matches[1]];
                continue;
            }

            list($k, $v) = explode(':', $row);

            if (!preg_match('/^db\d+$/', $k)) {
                if ($k === 'allocation_stats') {
                    $current[$k] = $this->parseAllocationStats($v);
                    continue;
                }
                $current[$k] = $v;
            }
            else {
                $current[$k] = $this->parseDatabaseStats($v);
            }
        }

        return $info;
    }
}

/**
 * @link http://redis.io/commands/flushall
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerFlushAll extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'FLUSHALL';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/dbsize
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerDatabaseSize extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'DBSIZE';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/config
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerConfig extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'CONFIG';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/**
 * @link http://redis.io/commands/ltrim
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListTrim extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'LTRIM';
    }
}

/**
 * @link http://redis.io/commands/lset
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListSet extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'LSET';
    }
}

/**
 * @link http://redis.io/commands/rpop
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListPopLast extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'RPOP';
    }
}

/**
 * @link http://redis.io/commands/brpop
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListPopLastBlocking extends ListPopFirstBlocking
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'BRPOP';
    }
}

/**
 * @link http://redis.io/commands/lpop
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListPopFirst extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'LPOP';
    }
}

/**
 * @link http://redis.io/commands/llen
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListLength extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'LLEN';
    }
}

/**
 * @link http://redis.io/commands/linsert
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListInsert extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'LINSERT';
    }
}

/**
 * @link http://redis.io/commands/rpoplpush
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListPopLastPushHead extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'RPOPLPUSH';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        return PrefixHelpers::multipleKeys($arguments, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return $this->checkSameHashForKeys($this->getArguments());
    }
}

/**
 * @link http://redis.io/commands/brpoplpush
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListPopLastPushHeadBlocking extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'BRPOPLPUSH';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        return PrefixHelpers::skipLastArgument($arguments, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return $this->checkSameHashForKeys(
            array_slice($args = $this->getArguments(), 0, count($args) - 1)
        );
    }
}

/**
 * @link http://redis.io/commands/lrange
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListRange extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'LRANGE';
    }
}

/**
 * @link http://redis.io/commands/lrem
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListRemove extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'LREM';
    }
}

/**
 * @link http://redis.io/commands/rpushx
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListPushTailX extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'RPUSHX';
    }
}

/**
 * @link http://redis.io/commands/lpushx
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListPushHeadX extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'LPUSHX';
    }
}

/**
 * @link http://redis.io/commands/lpush
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ListPushHead extends ListPushTail
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'LPUSH';
    }
}

/**
 * @link http://redis.io/commands/lastsave
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerLastSave extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'LASTSAVE';
    }

    /**
     * {@inheritdoc}
     */
    protected function onPrefixKeys(Array $arguments, $prefix)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeHashed()
    {
        return false;
    }
}

/* --------------------------------------------------------------------------- */

namespace Predis;

use Predis\Commands\ICommand;
use Predis\Network\IConnection;
use Predis\Network\IConnectionSingle;
use Predis\Profiles\IServerProfile;
use Predis\Profiles\ServerProfile;
use Predis\Pipeline\PipelineContext;
use Predis\Transaction\MultiExecContext;
use Predis\Options\IOption;
use Predis\Options\ClientPrefix;
use Predis\Options\ClientProfile;
use Predis\Options\ClientCluster;
use Predis\Options\ClientConnectionFactory;
use Predis\IConnectionParameters;
use Predis\Network\IConnectionCluster;

/**
 * Base exception class for Predis-related errors.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
abstract class PredisException extends \Exception
{
}

/**
 * Represents a complex reply object from Redis.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IReplyObject
{
}

/**
 * Base exception class for network-related errors.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
abstract class CommunicationException extends PredisException
{
    private $_connection;

    /**
     * @param IConnectionSingle $connection Connection that generated the exception.
     * @param string $message Error message.
     * @param int $code Error code.
     * @param \Exception $innerException Inner exception for wrapping the original error.
     */
    public function __construct(IConnectionSingle $connection,
        $message = null, $code = null, \Exception $innerException = null)
    {
        parent::__construct($message, $code, $innerException);

        $this->_connection = $connection;
    }

    /**
     * Gets the connection that generated the exception.
     *
     * @return IConnectionSingle
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Indicates if the receiver should reset the underlying connection.
     *
     * @return Boolean
     */
    public function shouldResetConnection()
    {
        return true;
    }
}

/**
 * Represents an error returned by Redis (replies identified by "-" in the
 * Redis response protocol) during the execution of an operation on the server.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IRedisServerError extends IReplyObject
{
    /**
     * Returns the error message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Returns the error type (e.g. ERR, ASK, MOVED)
     *
     * @return string
     */
    public function getErrorType();
}

/**
 * Interface that must be implemented by classes that provide their own mechanism
 * to parse and handle connection parameters.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IConnectionParameters
{
    /**
     * Checks if the specified parameters is set.
     *
     * @param string $property Name of the property.
     * @return Boolean
     */
    public function __isset($parameter);

    /**
     * Returns the value of the specified parameter.
     *
     * @param string $parameter Name of the parameter.
     * @return mixed
     */
    public function __get($parameter);

    /**
     * Returns an array representation of the connection parameters.
     *
     * @return array
     */
    public function toArray();
}

/**
 * Interface that must be implemented by classes that provide their own mechanism
 * to create and initialize new instances of Predis\Network\IConnectionSingle.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IConnectionFactory
{
    /**
     * Creates a new connection object.
     *
     * @param mixed $parameters Parameters for the connection.
     * @return Predis\Network\IConnectionSingle
     */
    public function create($parameters);
}

/**
 * Client-side abstraction of a Publish / Subscribe context.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class PubSubContext implements \Iterator
{
    const SUBSCRIBE    = 'subscribe';
    const UNSUBSCRIBE  = 'unsubscribe';
    const PSUBSCRIBE   = 'psubscribe';
    const PUNSUBSCRIBE = 'punsubscribe';
    const MESSAGE      = 'message';
    const PMESSAGE     = 'pmessage';

    const STATUS_VALID       = 0x0001;
    const STATUS_SUBSCRIBED  = 0x0010;
    const STATUS_PSUBSCRIBED = 0x0100;

    private $_client;
    private $_position;
    private $_options;

    /**
     * @param Client Client instance used by the context.
     * @param array Options for the context initialization.
     */
    public function __construct(Client $client, Array $options = null)
    {
        $this->checkCapabilities($client);
        $this->_options = $options ?: array();
        $this->_client = $client;
        $this->_statusFlags = self::STATUS_VALID;

        $this->genericSubscribeInit('subscribe');
        $this->genericSubscribeInit('psubscribe');
    }

    /**
     * Automatically closes the context when PHP's garbage collector kicks in.
     */
    public function __destruct()
    {
        $this->closeContext();
    }

    /**
     * Checks if the passed client instance satisfies the required conditions
     * needed to initialize a Publish / Subscribe context.
     *
     * @param Client Client instance used by the context.
     */
    private function checkCapabilities(Client $client)
    {
        if (Helpers::isCluster($client->getConnection())) {
            throw new ClientException(
                'Cannot initialize a PUB/SUB context over a cluster of connections'
            );
        }

        $commands = array('publish', 'subscribe', 'unsubscribe', 'psubscribe', 'punsubscribe');

        if ($client->getProfile()->supportsCommands($commands) === false) {
            throw new ClientException(
                'The current profile does not support PUB/SUB related commands'
            );
        }
    }

    /**
     * This method shares the logic to handle both SUBSCRIBE and PSUBSCRIBE.
     *
     * @param string $subscribeAction Type of subscription.
     */
    private function genericSubscribeInit($subscribeAction)
    {
        if (isset($this->_options[$subscribeAction])) {
            $this->$subscribeAction($this->_options[$subscribeAction]);
        }
    }

    /**
     * Checks if the specified flag is valid in the state of the context.
     *
     * @param int $value Flag.
     * @return Boolean
     */
    private function isFlagSet($value)
    {
        return ($this->_statusFlags & $value) === $value;
    }

    /**
     * Subscribes to the specified channels.
     *
     * @param mixed $arg,... One or more channel names.
     */
    public function subscribe(/* arguments */)
    {
        $this->writeCommand(self::SUBSCRIBE, func_get_args());
        $this->_statusFlags |= self::STATUS_SUBSCRIBED;
    }

    /**
     * Unsubscribes from the specified channels.
     *
     * @param mixed $arg,... One or more channel names.
     */
    public function unsubscribe(/* arguments */)
    {
        $this->writeCommand(self::UNSUBSCRIBE, func_get_args());
    }

    /**
     * Subscribes to the specified channels using a pattern.
     *
     * @param mixed $arg,... One or more channel name patterns.
     */
    public function psubscribe(/* arguments */)
    {
        $this->writeCommand(self::PSUBSCRIBE, func_get_args());
        $this->_statusFlags |= self::STATUS_PSUBSCRIBED;
    }

    /**
     * Unsubscribes from the specified channels using a pattern.
     *
     * @param mixed $arg,... One or more channel name patterns.
     */
    public function punsubscribe(/* arguments */)
    {
        $this->writeCommand(self::PUNSUBSCRIBE, func_get_args());
    }

    /**
     * Closes the context by unsubscribing from all the subscribed channels.
     */
    public function closeContext()
    {
        if ($this->valid()) {
            if ($this->isFlagSet(self::STATUS_SUBSCRIBED)) {
                $this->unsubscribe();
            }
            if ($this->isFlagSet(self::STATUS_PSUBSCRIBED)) {
                $this->punsubscribe();
            }
        }
    }

    /**
     * Writes a Redis command on the underlying connection.
     *
     * @param string $method ID of the command.
     * @param array $arguments List of arguments.
     */
    private function writeCommand($method, $arguments)
    {
        $arguments = Helpers::filterArrayArguments($arguments);
        $command = $this->_client->createCommand($method, $arguments);
        $this->_client->getConnection()->writeCommand($command);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        // NOOP
    }

    /**
     * Returns the last message payload retrieved from the server and generated
     * by one of the active subscriptions.
     *
     * @return array
     */
    public function current()
    {
        return $this->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if ($this->isFlagSet(self::STATUS_VALID)) {
            $this->_position++;
        }

        return $this->_position;
    }

    /**
     * Checks if the the context is still in a valid state to continue.
     *
     * @return Boolean
     */
    public function valid()
    {
        $isValid = $this->isFlagSet(self::STATUS_VALID);
        $subscriptionFlags = self::STATUS_SUBSCRIBED | self::STATUS_PSUBSCRIBED;
        $hasSubscriptions = ($this->_statusFlags & $subscriptionFlags) > 0;

        return $isValid && $hasSubscriptions;
    }

    /**
     * Resets the state of the context.
     */
    private function invalidate()
    {
        $this->_statusFlags = 0x0000;
    }

    /**
     * Waits for a new message from the server generated by one of the active
     * subscriptions and returns it when available.
     *
     * @return array
     */
    private function getValue()
    {
        $response = $this->_client->getConnection()->read();

        switch ($response[0]) {
            case self::SUBSCRIBE:
            case self::UNSUBSCRIBE:
            case self::PSUBSCRIBE:
            case self::PUNSUBSCRIBE:
                if ($response[2] === 0) {
                    $this->invalidate();
                }

            case self::MESSAGE:
                return (object) array(
                    'kind'    => $response[0],
                    'channel' => $response[1],
                    'payload' => $response[2],
                );

            case self::PMESSAGE:
                return (object) array(
                    'kind'    => $response[0],
                    'pattern' => $response[1],
                    'channel' => $response[2],
                    'payload' => $response[3],
                );

            default:
                throw new ClientException(
                    "Received an unknown message type {$response[0]} inside of a pubsub context"
                );
        }
    }
}

/**
 * Main class that exposes the most high-level interface to interact with Redis.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class Client
{
    const VERSION = '0.7.0-dev';

    private $_options;
    private $_profile;
    private $_connection;
    private $_connectionFactory;

    /**
     * Initializes a new client with optional connection parameters and client options.
     *
     * @param mixed $parameters Connection parameters for one or multiple servers.
     * @param mixed $options Options that specify certain behaviours for the client.
     */
    public function __construct($parameters = null, $options = null)
    {
        $options = $this->filterOptions($options);
        $profile = $options->profile;

        if (isset($options->prefix)) {
            $profile->setProcessor($options->prefix);
        }

        $this->_options = $options;
        $this->_profile = $profile;
        $this->_connectionFactory = $options->connections;
        $this->_connection = $this->initializeConnection($parameters);
    }

    /**
     * Creates an instance of Predis\Options\ClientOptions from various types of
     * arguments (string, array, Predis\Profiles\ServerProfile) or returns the
     * passed object if it is an instance of Predis\Options\ClientOptions.
     *
     * @param mixed $options Client options.
     * @return ClientOptions
     */
    private function filterOptions($options)
    {
        if ($options === null) {
            return new ClientOptions();
        }
        if (is_array($options)) {
            return new ClientOptions($options);
        }
        if ($options instanceof ClientOptions) {
            return $options;
        }
        if ($options instanceof IServerProfile) {
            return new ClientOptions(array('profile' => $options));
        }
        if (is_string($options)) {
            return new ClientOptions(array('profile' => ServerProfile::get($options)));
        }

        throw new \InvalidArgumentException("Invalid type for client options");
    }

    /**
     * Initializes one or multiple connection (cluster) objects from various
     * types of arguments (string, array) or returns the passed object if it
     * implements the Predis\Network\IConnection interface.
     *
     * @param mixed $parameters Connection parameters or instance.
     * @return IConnection
     */
    private function initializeConnection($parameters)
    {
        if ($parameters === null) {
            return $this->createConnection(new ConnectionParameters());
        }

        if (is_array($parameters)) {
            if (isset($parameters[0])) {
                $cluster = $this->_options->cluster;
                foreach ($parameters as $node) {
                    $connection = $node instanceof IConnectionSingle ? $node : $this->createConnection($node);
                    $cluster->add($connection);
                }
                return $cluster;
            }
            return $this->createConnection($parameters);
        }

        if ($parameters instanceof IConnection) {
            return $parameters;
        }

        return $this->createConnection($parameters);
    }

    /**
     * Creates a new connection to a single server with the provided parameters.
     *
     * @param mixed $parameters Connection parameters.
     * @return IConnectionSingle
     */
    protected function createConnection($parameters)
    {
        $connection = $this->_connectionFactory->create($parameters);
        $parameters = $connection->getParameters();

        if (isset($parameters->password)) {
            $command = $this->createCommand('auth', array($parameters->password));
            $connection->pushInitCommand($command);
        }

        if (isset($parameters->database)) {
            $command = $this->createCommand('select', array($parameters->database));
            $connection->pushInitCommand($command);
        }

        return $connection;
    }

    /**
     * Returns the server profile used by the client.
     *
     * @return IServerProfile
     */
    public function getProfile()
    {
        return $this->_profile;
    }

    /**
     * Returns the client options specified upon initialization.
     *
     * @return ClientOptions
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Returns the connection factory object used by the client.
     *
     * @return IConnectionFactory
     */
    public function getConnectionFactory()
    {
        return $this->_connectionFactory;
    }

    /**
     * Returns a new instance of a client for the specified connection when the
     * client is connected to a cluster. The new instance will use the same
     * options of the original client.
     *
     * @return Client
     */
    public function getClientFor($connectionAlias)
    {
        if (($connection = $this->getConnection($connectionAlias)) === null) {
            throw new \InvalidArgumentException("Invalid connection alias: '$connectionAlias'");
        }

        return new Client($connection, $this->_options);
    }

    /**
     * Opens the connection to the server.
     */
    public function connect()
    {
        $this->_connection->connect();
    }

    /**
     * Disconnects from the server.
     */
    public function disconnect()
    {
        $this->_connection->disconnect();
    }

    /**
     * Disconnects from the server.
     * 
     * This method is an alias of disconnect().
     */
    public function quit()
    {
        $this->disconnect();
    }

    /**
     * Checks if the underlying connection is connected to Redis.
     *
     * @return Boolean True means that the connection is open.
     *                 False means that the connection is closed.
     */
    public function isConnected()
    {
        return $this->_connection->isConnected();
    }

    /**
     * Returns the underlying connection instance or, when connected to a cluster,
     * one of the connection instances identified by its alias.
     *
     * @param string $id The alias of a connection when connected to a cluster.
     * @return IConnection
     */
    public function getConnection($id = null)
    {
        if (isset($id)) {
            if (!Helpers::isCluster($this->_connection)) {
                throw new ClientException(
                    'Retrieving connections by alias is supported only with clustered connections'
                );
            }

            return $this->_connection->getConnectionById($id);
        }

        return $this->_connection;
    }

    /**
     * Dinamically invokes a Redis command with the specified arguments.
     *
     * @param string $method The name of a Redis command.
     * @param array $arguments The arguments for the command.
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $command = $this->_profile->createCommand($method, $arguments);
        return $this->_connection->executeCommand($command);
    }

    /**
     * Creates a new instance of the specified Redis command.
     *
     * @param string $method The name of a Redis command.
     * @param array $arguments The arguments for the command.
     * @return ICommand
     */
    public function createCommand($method, $arguments = array())
    {
        return $this->_profile->createCommand($method, $arguments);
    }

    /**
     * Executes the specified Redis command.
     *
     * @param ICommand $command A Redis command.
     * @return mixed
     */
    public function executeCommand(ICommand $command)
    {
        return $this->_connection->executeCommand($command);
    }

    /**
     * Executes the specified Redis command on all the nodes of a cluster.
     *
     * @param ICommand $command A Redis command.
     * @return array
     */
    public function executeCommandOnShards(ICommand $command)
    {
        if (Helpers::isCluster($this->_connection)) {
            $replies = array();

            foreach ($this->_connection as $connection) {
                $replies[] = $connection->executeCommand($command);
            }

            return $replies;
        }

        return array($this->_connection->executeCommand($command));
    }

    /**
     * Calls the specified initializer method on $this with 0, 1 or 2 arguments.
     *
     * TODO: Invert $argv and $initializer.
     *
     * @param array $argv Arguments for the initializer.
     * @param string $initializer The initializer method.
     * @return mixed
     */
    private function sharedInitializer($argv, $initializer)
    {
        switch (count($argv)) {
            case 0:
                return $this->$initializer();

            case 1:
                list($arg0) = $argv;
                return is_array($arg0) ? $this->$initializer($arg0) : $this->$initializer(null, $arg0);

            case 2:
                list($arg0, $arg1) = $argv;
                return $this->$initializer($arg0, $arg1);

            default:
                return $this->$initializer($this, $argv);
        }
    }

    /**
     * Creates a new pipeline context and returns it, or returns the results of
     * a pipeline executed inside the optionally provided callable object.
     *
     * @param mixed $arg,... Options for the context, a callable object, or both.
     * @return PipelineContext|array
     */
    public function pipeline(/* arguments */)
    {
        return $this->sharedInitializer(func_get_args(), 'initPipeline');
    }

    /**
     * Pipeline context initializer.
     *
     * @param array $options Options for the context.
     * @param mixed $callable Optional callable object used to execute the context.
     * @return PipelineContext|array
     */
    protected function initPipeline(Array $options = null, $callable = null)
    {
        $pipeline = new PipelineContext($this, $options);
        return $this->pipelineExecute($pipeline, $callable);
    }

    /**
     * Executes a pipeline context when a callable object is passed.
     *
     * @param array $options Options of the context initialization.
     * @param mixed $callable Optional callable object used to execute the context.
     * @return PipelineContext|array
     */
    private function pipelineExecute(PipelineContext $pipeline, $callable)
    {
        return isset($callable) ? $pipeline->execute($callable) : $pipeline;
    }

    /**
     * Creates a new transaction context and returns it, or returns the results of
     * a transaction executed inside the optionally provided callable object.
     *
     * @param mixed $arg,... Options for the context, a callable object, or both.
     * @return MultiExecContext|array
     */
    public function multiExec(/* arguments */)
    {
        return $this->sharedInitializer(func_get_args(), 'initMultiExec');
    }

    /**
     * Transaction context initializer.
     *
     * @param array $options Options for the context.
     * @param mixed $callable Optional callable object used to execute the context.
     * @return MultiExecContext|array
     */
    protected function initMultiExec(Array $options = null, $callable = null)
    {
        $transaction = new MultiExecContext($this, $options ?: array());
        return isset($callable) ? $transaction->execute($callable) : $transaction;
    }

    /**
     * Creates a new Publish / Subscribe context and returns it, or executes it
     * inside the optionally provided callable object.
     *
     * @param mixed $arg,... Options for the context, a callable object, or both.
     * @return MultiExecContext|array
     */
    public function pubSub(/* arguments */)
    {
        return $this->sharedInitializer(func_get_args(), 'initPubSub');
    }

    /**
     * Publish / Subscribe context initializer.
     *
     * @param array $options Options for the context.
     * @param mixed $callable Optional callable object used to execute the context.
     * @return PubSubContext
     */
    protected function initPubSub(Array $options = null, $callable = null)
    {
        $pubsub = new PubSubContext($this, $options);

        if (!isset($callable)) {
            return $pubsub;
        }

        foreach ($pubsub as $message) {
            if ($callable($pubsub, $message) === false) {
                $pubsub->closeContext();
            }
        }
    }

    /**
     * Returns a new monitor context.
     *
     * @return MonitorContext
     */
    public function monitor()
    {
        return new MonitorContext($this);
    }
}

/**
 * Represents an error returned by Redis (-ERR replies) during the execution
 * of a command on the server.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ResponseError implements IRedisServerError
{
    private $_message;

    /**
     * @param string $message Error message returned by Redis
     */
    public function __construct($message)
    {
        $this->_message = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorType()
    {
        list($errorType, ) = explode(' ', $this->getMessage(), 2);
        return $errorType;
    }

    /**
     * Converts the object to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getMessage();
    }
}

/**
 * Represents a +QUEUED response returned by Redis as a reply to each command
 * executed inside a MULTI/ EXEC transaction.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ResponseQueued implements IReplyObject
{
    /**
     * Converts the object to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return 'QUEUED';
    }

    /**
     * Returns the value of the specified property.
     *
     * @param string $property Name of the property.
     * @return mixed
     */
    public function __get($property)
    {
        return $property === 'queued';
    }

    /**
     * Checks if the specified property is set.
     *
     * @param string $property Name of the property.
     * @return Boolean
     */
    public function __isset($property)
    {
        return $property === 'queued';
    }
}

/**
 * Exception class that identifies server-side Redis errors.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerException extends PredisException implements IRedisServerError
{
    /**
     * Gets the type of the error returned by Redis.
     *
     * @return string
     */
    public function getErrorType()
    {
        list($errorType, ) = explode(' ', $this->getMessage(), 2);
        return $errorType;
    }

    /**
     * Converts the exception to an instance of ResponseError.
     *
     * @return ResponseError
     */
    public function toResponseError()
    {
        return new ResponseError($this->getMessage());
    }
}

/**
 * Client-side abstraction of a Redis MONITOR context.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class MonitorContext implements \Iterator
{
    private $_client;
    private $_isValid;
    private $_position;

    /**
     * @param Client Client instance used by the context.
     */
    public function __construct(Client $client)
    {
        $this->checkCapabilities($client);
        $this->_client = $client;
        $this->openContext();
    }

    /**
     * Automatically closes the context when PHP's garbage collector kicks in.
     */
    public function __destruct()
    {
        $this->closeContext();
    }

    /**
     * Checks if the passed client instance satisfies the required conditions
     * needed to initialize a monitor context.
     *
     * @param Client Client instance used by the context.
     */
    private function checkCapabilities(Client $client)
    {
        if (Helpers::isCluster($client->getConnection())) {
            throw new ClientException(
                'Cannot initialize a monitor context over a cluster of connections'
            );
        }

        if ($client->getProfile()->supportsCommand('monitor') === false) {
            throw new ClientException(
                'The current profile does not support the MONITOR command'
            );
        }
    }

    /**
     * Initializes the context and sends the MONITOR command to the server.
     *
     * @param Client Client instance used by the context.
     */
    protected function openContext()
    {
        $this->_isValid = true;
        $monitor = $this->_client->createCommand('monitor');
        $this->_client->executeCommand($monitor);
    }

    /**
     * Closes the context. Internally this is done by disconnecting from server
     * since there is no way to terminate the stream initialized by MONITOR.
     */
    public function closeContext()
    {
        $this->_client->disconnect();
        $this->_isValid = false;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        // NOOP
    }

    /**
     * Returns the last message payload retrieved from the server.
     *
     * @return Object
     */
    public function current()
    {
        return $this->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->_position++;
    }

    /**
     * Checks if the the context is still in a valid state to continue.
     *
     * @return Boolean
     */
    public function valid()
    {
        return $this->_isValid;
    }

    /**
     * Waits for a new message from the server generated by MONITOR and
     * returns it when available.
     *
     * @return Object
     */
    private function getValue()
    {
        $database = 0;
        $event = $this->_client->getConnection()->read();

        $callback = function($matches) use (&$database) {
            if (isset($matches[1])) {
                $database = (int) $matches[1];
            }
            return ' ';
        };

        $event = preg_replace_callback('/ \(db (\d+)\) /', $callback, $event, 1);
        @list($timestamp, $command, $arguments) = split(' ', $event, 3);

        return (object) array(
            'timestamp' => (float) $timestamp,
            'database'  => $database,
            'command'   => substr($command, 1, -1),
            'arguments' => $arguments,
        );
    }
}

/**
 * Exception class that identifies client-side errors.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ClientException extends PredisException
{
}

/**
 * Defines a few helper methods.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class Helpers
{
    /**
     * Checks if the specified connection represents a cluster.
     *
     * @param IConnection $connection Connection object.
     * @return Boolean
     */
    public static function isCluster(IConnection $connection)
    {
        return $connection instanceof IConnectionCluster;
    }

    /**
     * Offers a generic and reusable method to handle exceptions generated by
     * a connection object.
     *
     * @param CommunicationException $exception Exception.
     */
    public static function onCommunicationException(CommunicationException $exception)
    {
        if ($exception->shouldResetConnection()) {
            $connection = $exception->getConnection();
            if ($connection->isConnected()) {
                $connection->disconnect();
            }
        }

        throw $exception;
    }

    /**
     * Normalizes the arguments array passed to a Redis command.
     *
     * @param array $arguments Arguments for a command.
     * @return array
     */
    public static function filterArrayArguments(Array $arguments)
    {
        if (count($arguments) === 1 && is_array($arguments[0])) {
            return $arguments[0];
        }

        return $arguments;
    }

    /**
     * Normalizes the arguments array passed to a variadic Redis command.
     *
     * @param array $arguments Arguments for a command.
     * @return array
     */
    public static function filterVariadicValues(Array $arguments)
    {
        if (count($arguments) === 2 && is_array($arguments[1])) {
            return array_merge(array($arguments[0]), $arguments[1]);
        }

        return $arguments;
    }

    /**
     * Returns only the hashable part of a key (delimited by "{...}"), or the
     * whole key if a key tag is not found in the string.
     *
     * @param string $key A key.
     * @return string
     */
    public static function getKeyHashablePart($key)
    {
        $start = strpos($key, '{');
        if ($start !== false) {
            $end = strpos($key, '}', $start);
            if ($end !== false) {
                $key = substr($key, ++$start, $end - $start);
            }
        }

        return $key;
    }
}

/**
 * Method-dispatcher loop built around the client-side abstraction of a Redis
 * Publish / Subscribe context.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class DispatcherLoop
{
    private $_client;
    private $_pubSubContext;
    private $_callbacks;
    private $_defaultCallback;
    private $_subscriptionCallback;

    /**
     * @param Client Client instance used by the context.
     */
    public function __construct(Client $client)
    {
        $this->_callbacks = array();
        $this->_client = $client;
        $this->_pubSubContext = $client->pubSub();
    }

    /**
     * Checks if the passed argument is a valid callback.
     *
     * @param mixed A callback.
     */
    protected function validateCallback($callable)
    {
        if (!is_callable($callable)) {
            throw new ClientException("A valid callable object must be provided");
        }
    }

    /**
     * Returns the underlying Publish / Subscribe context.
     *
     * @return PubSubContext
     */
    public function getPubSubContext()
    {
        return $this->_pubSubContext;
    }

    /**
     * Sets a callback that gets invoked upon new subscriptions.
     *
     * @param mixed $callable A callback.
     */
    public function subscriptionCallback($callable = null)
    {
        if (isset($callable)) {
            $this->validateCallback($callable);
        }
        $this->_subscriptionCallback = $callable;
    }

    /**
     * Sets a callback that gets invoked when a message is received on a
     * channel that does not have an associated callback.
     *
     * @param mixed $callable A callback.
     */
    public function defaultCallback($callable = null)
    {
        if (isset($callable)) {
            $this->validateCallback($callable);
        }
        $this->_subscriptionCallback = $callable;
    }

    /**
     * Binds a callback to a channel.
     *
     * @param string $channel Channel name.
     * @param Callable $callback A callback.
     */
    public function attachCallback($channel, $callback)
    {
        $this->validateCallback($callback);
        $this->_callbacks[$channel] = $callback;
        $this->_pubSubContext->subscribe($channel);
    }

    /**
     * Stops listening to a channel and removes the associated callback.
     *
     * @param string $channel Redis channel.
     */
    public function detachCallback($channel)
    {
        if (isset($this->_callbacks[$channel])) {
            unset($this->_callbacks[$channel]);
            $this->_pubSubContext->unsubscribe($channel);
        }
    }

    /**
     * Starts the dispatcher loop.
     */
    public function run()
    {
        foreach ($this->_pubSubContext as $message) {
            $kind = $message->kind;

            if ($kind !== PubSubContext::MESSAGE && $kind !== PubSubContext::PMESSAGE) {
                if (isset($this->_subscriptionCallback)) {
                    $callback = $this->_subscriptionCallback;
                    $callback($message);
                }
                continue;
            }

            if (isset($this->_callbacks[$message->channel])) {
                $callback = $this->_callbacks[$message->channel];
                $callback($message->payload);
            }
            else if (isset($this->_defaultCallback)) {
                $callback = $this->_defaultCallback;
                $callback($message);
            }
        }
    }

    /**
     * Terminates the dispatcher loop.
     */
    public function stop()
    {
        $this->_pubSubContext->closeContext();
    }
}

/**
 * Implements a lightweight PSR-0 compliant autoloader.
 *
 * @author Eric Naeseth <eric@thumbtack.com>
 */
class Autoloader
{
    private $_baseDir;
    private $_prefix;

    /**
     * @param string $baseDirectory Base directory where the source files are located.
     */
    public function __construct($baseDirectory = null)
    {
        $this->_baseDir = $baseDirectory ?: dirname(__FILE__);
        $this->_prefix = __NAMESPACE__ . '\\';
    }

    /**
     * Registers the autoloader class with the PHP SPL autoloader.
     */
    public static function register()
    {
        spl_autoload_register(array(new self, 'autoload'));
    }

    /**
     * Loads a class from a file using its fully qualified name.
     *
     * @param string $className Fully qualified name of a class.
     */
    public function autoload($className)
    {
        if (0 !== strpos($className, $this->_prefix)) {
            return;
        }

        $relativeClassName = substr($className, strlen($this->_prefix));
        $classNameParts = explode('\\', $relativeClassName);

        $path = $this->_baseDir .
            DIRECTORY_SEPARATOR .
            implode(DIRECTORY_SEPARATOR, $classNameParts) .
            '.php';

        require_once $path;
    }
}

/**
 * Provides a default factory for Redis connections that maps URI schemes
 * to connection classes implementing the Predis\Network\IConnectionSingle
 * interface.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ConnectionFactory implements IConnectionFactory
{
    private static $_globalSchemes;

    private $_instanceSchemes = array();

    /**
     * @param array $schemesMap Map of URI schemes to connection classes.
     */
    public function __construct(Array $schemesMap = null)
    {
        $this->_instanceSchemes = self::ensureDefaultSchemes();

        if (isset($schemesMap)) {
            foreach ($schemesMap as $scheme => $initializer) {
                $this->defineConnection($scheme, $initializer);
            }
        }
    }

    /**
     * Checks if the provided argument represents a valid connection class
     * implementing the Predis\Network\IConnectionSingle interface. Optionally,
     * callable objects are used for lazy initialization of connection objects.
     *
     * @param mixed $initializer FQN of a connection class or a callable for lazy initialization.
     */
    private static function checkConnectionInitializer($initializer)
    {
        if (is_callable($initializer)) {
            return;
        }

        $initializerReflection = new \ReflectionClass($initializer);

        if (!$initializerReflection->isSubclassOf('\Predis\Network\IConnectionSingle')) {
            throw new \InvalidArgumentException(
                'A connection initializer must be a valid connection class or a callable object'
            );
        }
    }

    /**
     * Ensures that the default global URI schemes map is initialized.
     *
     * @return array
     */
    private static function ensureDefaultSchemes()
    {
        if (!isset(self::$_globalSchemes)) {
            self::$_globalSchemes = array(
                'tcp'   => '\Predis\Network\StreamConnection',
                'unix'  => '\Predis\Network\StreamConnection',
            );
        }

        return self::$_globalSchemes;
    }

    /**
     * Defines a new URI scheme => connection class relation at class level.
     *
     * @param string $scheme URI scheme
     * @param mixed $connectionInitializer FQN of a connection class or a callable for lazy initialization.
     */
    public static function define($scheme, $connectionInitializer)
    {
        self::ensureDefaultSchemes();
        self::checkConnectionInitializer($connectionInitializer);
        self::$_globalSchemes[$scheme] = $connectionInitializer;
    }

    /**
     * Defines a new URI scheme => connection class relation at instance level.
     *
     * @param string $scheme URI scheme
     * @param mixed $connectionInitializer FQN of a connection class or a callable for lazy initialization.
     */
    public function defineConnection($scheme, $connectionInitializer)
    {
        self::checkConnectionInitializer($connectionInitializer);
        $this->_instanceSchemes[$scheme] = $connectionInitializer;
    }

    /**
     * {@inheritdoc}
     */
    public function create($parameters)
    {
        if (!$parameters instanceof IConnectionParameters) {
            $parameters = new ConnectionParameters($parameters);
        }

        $scheme = $parameters->scheme;
        if (!isset($this->_instanceSchemes[$scheme])) {
            throw new \InvalidArgumentException("Unknown connection scheme: $scheme");
        }

        $initializer = $this->_instanceSchemes[$scheme];
        if (!is_callable($initializer)) {
            return new $initializer($parameters);
        }

        $connection = call_user_func($initializer, $parameters);
        if (!$connection instanceof IConnectionSingle) {
            throw new \InvalidArgumentException(
                'Objects returned by connection initializers must implement ' .
                'the Predis\Network\IConnectionSingle interface'
            );
        }

        return $connection;
    }
}

/**
 * Class that manages validation and conversion of client options.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ClientOptions
{
    private static $_sharedOptions;

    private $_handlers;
    private $_defined;

    private $_options = array();

    /**
     * @param array $options Array of client options.
     */
    public function __construct(Array $options = array())
    {
        $this->_handlers = $this->initialize($options);
        $this->_defined = array_keys($options);
    }

    /**
     * Ensures that the default options are initialized.
     *
     * @return array
     */
    private static function getSharedOptions()
    {
        if (isset(self::$_sharedOptions)) {
            return self::$_sharedOptions;
        }

        self::$_sharedOptions = array(
            'profile' => new ClientProfile(),
            'connections' => new ClientConnectionFactory(),
            'cluster' => new ClientCluster(),
            'prefix' => new ClientPrefix(),
        );

        return self::$_sharedOptions;
    }

    /**
     * Defines an option handler or overrides an existing one.
     *
     * @param string $option Name of the option.
     * @param IOption $handler Handler for the option.
     */
    public static function define($option, IOption $handler)
    {
        self::getSharedOptions();
        self::$_sharedOptions[$option] = $handler;
    }

    /**
     * Undefines the handler for the specified option.
     *
     * @param string $option Name of the option.
     */
    public static function undefine($option)
    {
        self::getSharedOptions();
        unset(self::$_sharedOptions[$option]);
    }

    /**
     * Initializes client options handlers.
     *
     * @param array $options List of client options values.
     * @return array
     */
    private function initialize($options)
    {
        $handlers = self::getSharedOptions();

        foreach ($options as $option => $value) {
            if (isset($handlers[$option])) {
                $handler = $handlers[$option];
                $handlers[$option] = function() use($handler, $value) {
                    return $handler->validate($value);
                };
            }
        }

        return $handlers;
    }

    /**
     * Checks if the specified option is set.
     *
     * @param string $option Name of the option.
     * @return Boolean
     */
    public function __isset($option)
    {
        return in_array($option, $this->_defined);
    }

    /**
     * Returns the value of the specified option.
     *
     * @param string $option Name of the option.
     * @return mixed
     */
    public function __get($option)
    {
        if (isset($this->_options[$option])) {
            return $this->_options[$option];
        }

        if (isset($this->_handlers[$option])) {
            $handler = $this->_handlers[$option];
            $value = $handler instanceof IOption ? $handler->getDefault() : $handler();
            $this->_options[$option] = $value;

            return $value;
        }
    }
}

/**
 * Handles parsing and validation of connection parameters.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ConnectionParameters implements IConnectionParameters
{
    private static $_defaultParameters;
    private static $_validators;

    private $_parameters;
    private $_userDefined;

    /**
     * @param string|array Connection parameters in the form of an URI string or a named array.
     */
    public function __construct($parameters = array())
    {
        self::ensureDefaults();

        if (!is_array($parameters)) {
            $parameters = $this->parseURI($parameters);
        }

        $this->_userDefined = array_keys($parameters);
        $this->_parameters = $this->filter($parameters) + self::$_defaultParameters;
    }

    /**
     * Ensures that the default values and validators are initialized.
     */
    private static function ensureDefaults()
    {
        if (!isset(self::$_defaultParameters)) {
            self::$_defaultParameters = array(
                'scheme' => 'tcp',
                'host' => '127.0.0.1',
                'port' => 6379,
                'database' => null,
                'password' => null,
                'connection_async' => false,
                'connection_persistent' => false,
                'connection_timeout' => 5.0,
                'read_write_timeout' => null,
                'alias' => null,
                'weight' => null,
                'path' => null,
                'iterable_multibulk' => false,
                'throw_errors' => true,
            );
        }

        if (!isset(self::$_validators)) {
            $bool = function($value) { return (bool) $value; };
            $float = function($value) { return (float) $value; };
            $int = function($value) { return (int) $value; };

            self::$_validators = array(
                'port' => $int,
                'connection_async' => $bool,
                'connection_persistent' => $bool,
                'connection_timeout' => $float,
                'read_write_timeout' => $float,
                'iterable_multibulk' => $bool,
                'throw_errors' => $bool,
            );
        }
    }

    /**
     * Defines a default value and a validator for the specified parameter.
     *
     * @param string $parameter Name of the parameter.
     * @param mixed $default Default value or an instance of IOption.
     * @param mixed $callable A validator callback.
     */
    public static function define($parameter, $default, $callable = null)
    {
        self::ensureDefaults();
        self::$_defaultParameters[$parameter] = $default;

        if ($default instanceof IOption) {
            self::$_validators[$parameter] = $default;
            return;
        }

        if (!isset($callable)) {
            unset(self::$_validators[$parameter]);
            return;
        }

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException(
                "The validator for $parameter must be a callable object"
            );
        }

        self::$_validators[$parameter] = $callable;
    }

    /**
     * Undefines the default value and validator for the specified parameter.
     *
     * @param string $parameter Name of the parameter.
     */
    public static function undefine($parameter)
    {
        self::ensureDefaults();
        unset(self::$_defaultParameters[$parameter], self::$_validators[$parameter]);
    }

    /**
     * Parses an URI string and returns an array of connection parameters.
     *
     * @param string $uri Connection string.
     * @return array
     */
    private function parseURI($uri)
    {
        if (stripos($uri, 'unix') === 0) {
            // Hack to support URIs for UNIX sockets with minimal effort.
            $uri = str_ireplace('unix:///', 'unix://localhost/', $uri);
        }

        if (($parsed = @parse_url($uri)) === false || !isset($parsed['host'])) {
            throw new ClientException("Invalid URI: $uri");
        }

        if (isset($parsed['query'])) {
            foreach (explode('&', $parsed['query']) as $kv) {
                @list($k, $v) = explode('=', $kv);
                $parsed[$k] = $v;
            }
            unset($parsed['query']);
        }

        return $parsed;
    }

    /**
     * Validates and converts each value of the connection parameters array.
     *
     * @param array $parameters Connection parameters.
     * @return array
     */
    private function filter(Array $parameters)
    {
        if (count($parameters) > 0) {
            $validators = array_intersect_key(self::$_validators, $parameters);
            foreach ($validators as $parameter => $validator) {
                $parameters[$parameter] = $validator($parameters[$parameter]);
            }
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function __get($parameter)
    {
        $value = $this->_parameters[$parameter];

        if ($value instanceof IOption) {
            $this->_parameters[$parameter] = ($value = $value->getDefault());
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($parameter)
    {
        return isset($this->_parameters[$parameter]);
    }

    /**
     * Checks if the specified parameter has been set by the user.
     *
     * @param string $parameter Name of the parameter.
     * @return Boolean
     */
    public function isSetByUser($parameter)
    {
        return in_array($parameter, $this->_userDefined);
    }

    /**
     * {@inheritdoc}
     */
    protected function getBaseURI()
    {
        if ($this->scheme === 'unix') {
            return "{$this->scheme}://{$this->path}";
        }

        return "{$this->scheme}://{$this->host}:{$this->port}";
    }

    /**
     * Returns the URI parts that must be omitted when calling __toString().
     *
     * @return array
     */
    protected function getDisallowedURIParts()
    {
        return array('scheme', 'host', 'port', 'password', 'path');
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->_parameters;
    }

    /**
     * Returns a string representation of the parameters.
     *
     * @return string
     */
    public function __toString()
    {
        $query = array();
        $parameters = $this->toArray();
        $reject = $this->getDisallowedURIParts();

        foreach ($this->_userDefined as $param) {
            if (in_array($param, $reject) || !isset($parameters[$param])) {
                continue;
            }
            $value = $parameters[$param];
            $query[] = "$param=" . ($value === false ? '0' : $value);
        }

        if (count($query) === 0) {
            return $this->getBaseURI();
        }

        return $this->getBaseURI() . '/?' . implode('&', $query);
    }

    /**
     * {@inheritdoc}
     */
    public function __sleep()
    {
        return array('_parameters', '_userDefined');
    }

    /**
     * {@inheritdoc}
     */
    public function __wakeup()
    {
        self::ensureDefaults();
    }
}

/* --------------------------------------------------------------------------- */

namespace Predis\Network;

use Predis\IConnectionParameters;
use Predis\Commands\ICommand;
use Predis\Protocol\IProtocolProcessor;
use Predis\Protocol\Text\TextProtocol;
use \InvalidArgumentException;
use Predis\Helpers;
use Predis\IReplyObject;
use Predis\ClientException;
use Predis\Protocol\ProtocolException;
use Predis\CommunicationException;
use Predis\ResponseError;
use Predis\ResponseQueued;
use Predis\ServerException;
use Predis\Distribution\IDistributionStrategy;
use Predis\Distribution\HashRing;
use Predis\Iterators\MultiBulkResponseSimple;

/**
 * Defines a connection object used to communicate with one or multiple
 * Redis servers.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IConnection
{
    /**
     * Opens the connection.
     */
    public function connect();

    /**
     * Closes the connection.
     */
    public function disconnect();

    /**
     * Returns if the connection is open.
     *
     * @return Boolean
     */
    public function isConnected();

    /**
     * Write a Redis command on the connection.
     *
     * @param ICommand $command Instance of a Redis command.
     */
    public function writeCommand(ICommand $command);

    /**
     * Reads the reply for a Redis command from the connection.
     *
     * @param ICommand $command Instance of a Redis command.
     * @return mixed
     */
    public function readResponse(ICommand $command);

    /**
     * Writes a Redis command to the connection and reads back the reply.
     *
     * @param ICommand $command Instance of a Redis command.
     * @return mixed
     */
    public function executeCommand(ICommand $command);
}

/**
 * Defines a connection object used to communicate with a single Redis server.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IConnectionSingle extends IConnection
{
    /**
     * Returns a string representation of the connection.
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns the underlying resource used to communicate with a Redis server.
     *
     * @return mixed
     */
    public function getResource();

    /**
     * Gets the parameters used to initialize the connection object.
     *
     * @return IConnectionParameters
     */
    public function getParameters();

    /**
     * Pushes the instance of a Redis command to the queue of commands executed
     * when the actual connection to a server is estabilished.
     *
     * @param ICommand $command Instance of a Redis command.
     * @return IConnectionParameters
     */
    public function pushInitCommand(ICommand $command);

    /**
     * Reads a reply from the server.
     *
     * @return mixed
     */
    public function read();
}

/**
 * Base class with the common logic used by connection classes to communicate with Redis.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
abstract class ConnectionBase implements IConnectionSingle
{
    private $_resource;
    private $_cachedId;

    protected $_params;
    protected $_initCmds;

    /**
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     */
    public function __construct(IConnectionParameters $parameters)
    {
        $this->_initCmds = array();
        $this->_params = $this->checkParameters($parameters);
        $this->initializeProtocol($parameters);
    }

    /**
     * Disconnects from the server and destroys the underlying resource when
     * PHP's garbage collector kicks in.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Checks some of the parameters used to initialize the connection.
     *
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     */
    protected function checkParameters(IConnectionParameters $parameters)
    {
        switch ($parameters->scheme) {
            case 'unix':
                if (!isset($parameters->path)) {
                    throw new InvalidArgumentException('Missing UNIX domain socket path');
                }

            case 'tcp':
                return $parameters;

            default:
                throw new InvalidArgumentException("Invalid scheme: {$parameters->scheme}");
        }
    }

    /**
     * Initializes some common configurations of the underlying protocol processor
     * from the connection parameters.
     *
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     */
    protected function initializeProtocol(IConnectionParameters $parameters)
    {
        // NOOP
    }

    /**
     * Creates the underlying resource used to communicate with Redis.
     *
     * @return mixed
     */
    protected abstract function createResource();

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        return isset($this->_resource);
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        if ($this->isConnected()) {
            throw new ClientException('Connection already estabilished');
        }
        $this->_resource = $this->createResource();
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        unset($this->_resource);
    }

    /**
     * {@inheritdoc}
     */
    public function pushInitCommand(ICommand $command)
    {
        $this->_initCmds[] = $command;
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand(ICommand $command)
    {
        $this->writeCommand($command);
        return $this->readResponse($command);
    }

    /**
     * {@inheritdoc}
     */
    public function readResponse(ICommand $command)
    {
        $reply = $this->read();

        if ($reply instanceof IReplyObject) {
            return $reply;
        }

        return $command->parseResponse($reply);
    }

    /**
     * Helper method to handle connection errors.
     *
     * @param string $message Error message.
     * @param int $code Error code.
     */
    protected function onConnectionError($message, $code = null)
    {
        Helpers::onCommunicationException(new ConnectionException($this, $message, $code));
    }

    /**
     * Helper method to handle protocol errors.
     *
     * @param string $message Error message.
     */
    protected function onProtocolError($message)
    {
        Helpers::onCommunicationException(new ProtocolException($this, $message));
    }

    /**
     * Helper method to handle invalid connection parameters.
     *
     * @param string $option Name of the option.
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     */
    protected function onInvalidOption($option, $parameters = null)
    {
        $message = "Invalid option: $option";
        if (isset($parameters)) {
            $message .= " [$parameters]";
        }

        throw new InvalidArgumentException($message);
    }

    /**
     * {@inheritdoc}
     */
    public function getResource()
    {
        if (isset($this->_resource)) {
            return $this->_resource;
        }

        $this->connect();

        return $this->_resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->_params;
    }

    /**
     * Gets an identifier for the connection.
     *
     * @return string
     */
    protected function getIdentifier()
    {
        if ($this->_params->scheme === 'unix') {
            return $this->_params->path;
        }

        return "{$this->_params->host}:{$this->_params->port}";
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (!isset($this->_cachedId)) {
            $this->_cachedId = $this->getIdentifier();
        }

        return $this->_cachedId;
    }
}

/**
 * Connection abstraction to Redis servers based on PHP's streams.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StreamConnection extends ConnectionBase
{
    private $_mbiterable;
    private $_throwErrors;

    /**
     * Disconnects from the server and destroys the underlying resource when
     * PHP's garbage collector kicks in only if the connection has not been
     * marked as persistent.
     */
    public function __destruct()
    {
        if (!$this->_params->connection_persistent) {
            $this->disconnect();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeProtocol(IConnectionParameters $parameters)
    {
        $this->_throwErrors = $parameters->throw_errors;
        $this->_mbiterable = $parameters->iterable_multibulk;
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource()
    {
        $parameters = $this->_params;
        $initializer = "{$parameters->scheme}StreamInitializer";

        return $this->$initializer($parameters);
    }

    /**
     * Initializes a TCP stream resource.
     *
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     * @return resource
     */
    private function tcpStreamInitializer(IConnectionParameters $parameters)
    {
        $uri = "tcp://{$parameters->host}:{$parameters->port}/";

        $flags = STREAM_CLIENT_CONNECT;
        if ($parameters->connection_async) {
            $flags |= STREAM_CLIENT_ASYNC_CONNECT;
        }
        if ($parameters->connection_persistent) {
            $flags |= STREAM_CLIENT_PERSISTENT;
        }

        $resource = @stream_socket_client(
            $uri, $errno, $errstr, $parameters->connection_timeout, $flags
        );

        if (!$resource) {
            $this->onConnectionError(trim($errstr), $errno);
        }

        if (isset($parameters->read_write_timeout)) {
            $rwtimeout = $parameters->read_write_timeout;
            $rwtimeout = $rwtimeout > 0 ? $rwtimeout : -1;
            $timeoutSeconds  = floor($rwtimeout);
            $timeoutUSeconds = ($rwtimeout - $timeoutSeconds) * 1000000;
            stream_set_timeout($resource, $timeoutSeconds, $timeoutUSeconds);
        }

        return $resource;
    }

    /**
     * Initializes a UNIX stream resource.
     *
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     * @return resource
     */
    private function unixStreamInitializer(IConnectionParameters $parameters)
    {
        $uri = "unix://{$parameters->path}";

        $flags = STREAM_CLIENT_CONNECT;
        if ($parameters->connection_persistent) {
            $flags |= STREAM_CLIENT_PERSISTENT;
        }

        $resource = @stream_socket_client(
            $uri, $errno, $errstr, $parameters->connection_timeout, $flags
        );

        if (!$resource) {
            $this->onConnectionError(trim($errstr), $errno);
        }

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        parent::connect();

        if (count($this->_initCmds) > 0){
            $this->sendInitializationCommands();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            fclose($this->getResource());

            parent::disconnect();
        }
    }

    /**
     * Sends the initialization commands to Redis when the connection is opened.
     */
    private function sendInitializationCommands()
    {
        foreach ($this->_initCmds as $command) {
            $this->writeCommand($command);
        }
        foreach ($this->_initCmds as $command) {
            $this->readResponse($command);
        }
    }

    /**
     * Performs a write operation on the stream of the buffer containing a
     * command serialized with the Redis wire protocol.
     *
     * @param string $buffer Redis wire protocol representation of a command.
     */
    protected function writeBytes($buffer)
    {
        $socket = $this->getResource();

        while (($length = strlen($buffer)) > 0) {
            $written = fwrite($socket, $buffer);
            if ($length === $written) {
                return;
            }
            if ($written === false || $written === 0) {
                $this->onConnectionError('Error while writing bytes to the server');
            }
            $buffer = substr($buffer, $written);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read() {
        $socket = $this->getResource();

        $chunk  = fgets($socket);
        if ($chunk === false || $chunk === '') {
            $this->onConnectionError('Error while reading line from the server');
        }

        $prefix  = $chunk[0];
        $payload = substr($chunk, 1, -2);

        switch ($prefix) {
            case '+':    // inline
                switch ($payload) {
                    case 'OK':
                        return true;

                    case 'QUEUED':
                        return new ResponseQueued();

                    default:
                        return $payload;
                }

            case '$':    // bulk
                $size = (int) $payload;
                if ($size === -1) {
                    return null;
                }

                $bulkData = '';
                $bytesLeft = ($size += 2);

                do {
                    $chunk = fread($socket, min($bytesLeft, 4096));
                    if ($chunk === false || $chunk === '') {
                        $this->onConnectionError(
                            'Error while reading bytes from the server'
                        );
                    }
                    $bulkData .= $chunk;
                    $bytesLeft = $size - strlen($bulkData);
                } while ($bytesLeft > 0);

                return substr($bulkData, 0, -2);

            case '*':    // multi bulk
                $count = (int) $payload;
                if ($count === -1) {
                    return null;
                }

                if ($this->_mbiterable === true) {
                    return new MultiBulkResponseSimple($this, $count);
                }

                $multibulk = array();
                for ($i = 0; $i < $count; $i++) {
                    $multibulk[$i] = $this->read();
                }

                return $multibulk;

            case ':':    // integer
                return (int) $payload;

            case '-':    // error
                if ($this->_throwErrors) {
                    throw new ServerException($payload);
                }
                return new ResponseError($payload);

            default:
                $this->onProtocolError("Unknown prefix: '$prefix'");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeCommand(ICommand $command)
    {
        $commandId = $command->getId();
        $arguments = $command->getArguments();

        $cmdlen = strlen($commandId);
        $reqlen = count($arguments) + 1;

        $buffer = "*{$reqlen}\r\n\${$cmdlen}\r\n{$commandId}\r\n";

        for ($i = 0; $i < $reqlen - 1; $i++) {
            $argument = $arguments[$i];
            $arglen = strlen($argument);
            $buffer .= "\${$arglen}\r\n{$argument}\r\n";
        }

        $this->writeBytes($buffer);
    }
}

/**
 * Defines a connection object used to communicate with a single Redis server
 * that leverages an external protocol processor to handle pluggable protocol
 * handlers.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IConnectionComposable extends IConnectionSingle
{
    /**
     * Sets the protocol processor used by the connection.
     *
     * @param IProtocolProcessor $protocol Protocol processor.
     */
    public function setProtocol(IProtocolProcessor $protocol);

    /**
     * Gets the protocol processor used by the connection.
     */
    public function getProtocol();

    /**
     * Writes a buffer that contains a serialized Redis command.
     *
     * @param string $buffer Serialized Redis command.
     */
    public function writeBytes($buffer);

    /**
     * Reads a specified number of bytes from the connection.
     *
     * @param string
     */
    public function readBytes($length);

    /**
     * Reads a line from the connection.
     *
     * @param string
     */
    public function readLine();
}

/**
 * Defines a cluster of Redis servers formed by aggregating multiple
 * connection objects.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IConnectionCluster extends IConnection
{
    /**
     * Adds a connection instance to the cluster.
     *
     * @param IConnectionSingle $connection Instance of a connection.
     */
    public function add(IConnectionSingle $connection);

    /**
     * Gets the actual connection instance in charge of the specified command.
     *
     * @param ICommand $command Instance of a Redis command.
     * @return IConnectionSingle
     */
    public function getConnection(ICommand $command);

    /**
     * Retrieves a connection instance from the cluster using an alias.
     *
     * @param string $connectionId Alias of a connection
     * @return IConnectionSingle
     */
    public function getConnectionById($connectionId);
}

const ERR_MSG_EXTENSION = 'The %s extension must be loaded in order to be able to use this connection class';

/**
 * This class implements a Predis connection that actually talks with Webdis
 * instead of connecting directly to Redis. It relies on the cURL extension to
 * communicate with the web server and the phpiredis extension to parse the
 * protocol of the replies returned in the http response bodies.
 *
 * Some features are not yet available or they simply cannot be implemented:
 *   - Pipelining commands.
 *   - Publish / Subscribe.
 *   - MULTI / EXEC transactions (not yet supported by Webdis).
 *
 * @link http://webd.is
 * @link http://github.com/nicolasff/webdis
 * @link http://github.com/seppo0010/phpiredis
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class WebdisConnection implements IConnectionSingle
{
    private $_parameters;
    private $_resource;
    private $_reader;

    /**
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     */
    public function __construct(IConnectionParameters $parameters)
    {
        $this->_parameters = $parameters;

        if ($parameters->scheme !== 'http') {
            throw new \InvalidArgumentException("Invalid scheme: {$parameters->scheme}");
        }

        $this->checkExtensions();
        $this->_resource = $this->initializeCurl($parameters);
        $this->_reader = $this->initializeReader($parameters);
    }

    /**
     * Frees the underlying cURL and protocol reader resources when PHP's
     * garbage collector kicks in.
     */
    public function __destruct()
    {
        curl_close($this->_resource);
        phpiredis_reader_destroy($this->_reader);
    }

    /**
     * Helper method used to throw on unsupported methods.
     */
    private function throwNotSupportedException($function)
    {
        $class = __CLASS__;
        throw new \RuntimeException("The method $class::$function() is not supported");
    }

    /**
     * Checks if the cURL and phpiredis extensions are loaded in PHP.
     */
    private function checkExtensions()
    {
        if (!function_exists('curl_init')) {
            throw new ClientException(sprintf(ERR_MSG_EXTENSION, 'curl'));
        }
        if (!function_exists('phpiredis_reader_create')) {
            throw new ClientException(sprintf(ERR_MSG_EXTENSION, 'phpiredis'));
        }
    }

    /**
     * Initializes cURL.
     *
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     * @return resource
     */
    private function initializeCurl(IConnectionParameters $parameters)
    {
        $options = array(
            CURLOPT_FAILONERROR => true,
            CURLOPT_CONNECTTIMEOUT_MS => $parameters->connection_timeout * 1000,
            CURLOPT_URL => "{$parameters->scheme}://{$parameters->host}:{$parameters->port}",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => true,
            CURLOPT_WRITEFUNCTION => array($this, 'feedReader'),
        );

        if (isset($parameters->user, $parameters->pass)) {
            $options[CURLOPT_USERPWD] = "{$parameters->user}:{$parameters->pass}";
        }

        $resource = curl_init();
        curl_setopt_array($resource, $options);

        return $resource;
    }

    /**
     * Initializes phpiredis' protocol reader.
     *
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     * @return resource
     */
    private function initializeReader(IConnectionParameters $parameters)
    {
        $reader = phpiredis_reader_create();

        phpiredis_reader_set_status_handler($reader, $this->getStatusHandler());
        phpiredis_reader_set_error_handler($reader, $this->getErrorHandler($parameters->throw_errors));

        return $reader;
    }

    /**
     * Gets the handler used by the protocol reader to handle status replies.
     *
     * @return \Closure
     */
    private function getStatusHandler()
    {
        return function($payload) {
            return $payload === 'OK' ? true : $payload;
        };
    }

    /**
     * Gets the handler used by the protocol reader to handle Redis errors.
     *
     * @param Boolean $throwErrors Specify if Redis errors throw exceptions.
     * @return \Closure
     */
    private function getErrorHandler($throwErrors)
    {
        if ($throwErrors) {
            return function($errorMessage) {
                throw new ServerException($errorMessage);
            };
        }

        return function($errorMessage) {
            return new ResponseError($errorMessage);
        };
    }

    /**
     * Feeds phpredis' reader resource with the data read from the network.
     *
     * @param resource $resource Reader resource.
     * @param string $buffer Buffer with the reply read from the network.
     * @return int
     */
    protected function feedReader($resource, $buffer)
    {
        phpiredis_reader_feed($this->_reader, $buffer);

        return strlen($buffer);
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        // NOOP
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        // NOOP
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        return true;
    }

    /**
     * Checks if the specified command is supported by this connection class.
     *
     * @param ICommand $command The instance of a Redis command.
     * @return string
     */
    protected function getCommandId(ICommand $command)
    {
        switch (($commandId = $command->getId())) {
            case 'AUTH':
            case 'SELECT':
            case 'MULTI':
            case 'EXEC':
            case 'WATCH':
            case 'UNWATCH':
            case 'DISCARD':
                throw new \InvalidArgumentException("Disabled command: {$command->getId()}");

            default:
                return $commandId;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeCommand(ICommand $command)
    {
        $this->throwNotSupportedException(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function readResponse(ICommand $command)
    {
        $this->throwNotSupportedException(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand(ICommand $command)
    {
        $resource = $this->_resource;
        $commandId = $this->getCommandId($command);

        if ($arguments = $command->getArguments()) {
            $arguments = implode('/', array_map('urlencode', $arguments));
            $serializedCommand = "$commandId/$arguments.raw";
        }
        else {
            $serializedCommand = "$commandId.raw";
        }

        curl_setopt($resource, CURLOPT_POSTFIELDS, $serializedCommand);

        if (curl_exec($resource) === false) {
            $error = curl_error($resource);
            $errno = curl_errno($resource);
            throw new ConnectionException($this, trim($error), $errno);
        }

        $readerState = phpiredis_reader_get_state($this->_reader);

        if ($readerState === PHPIREDIS_READER_STATE_COMPLETE) {
            $reply = phpiredis_reader_get_reply($this->_reader);
            if ($reply instanceof IReplyObject) {
                return $reply;
            }
            return $command->parseResponse($reply);
        }
        else {
            $error = phpiredis_reader_get_error($this->_reader);
            throw new ProtocolException($this, $error);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function pushInitCommand(ICommand $command)
    {
        $this->throwNotSupportedException(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $this->throwNotSupportedException(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return "{$this->_parameters->host}:{$this->_parameters->port}";
    }
}

/**
 * Exception class that identifies connection-related errors.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ConnectionException extends CommunicationException
{
}

/**
 * This class provides the implementation of a Predis connection that uses the
 * PHP socket extension for network communication and wraps the phpiredis C
 * extension (PHP bindings for hiredis) to parse the Redis protocol. Everything
 * is highly experimental (even the very same phpiredis since it is quite new),
 * so use it at your own risk.
 *
 * This class is mainly intended to provide an optional low-overhead alternative
 * for processing replies from Redis compared to the standard pure-PHP classes.
 * Differences in speed when dealing with short inline replies are practically
 * nonexistent, the actual speed boost is for long multibulk replies when this
 * protocol processor can parse and return replies very fast.
 *
 * For instructions on how to build and install the phpiredis extension, please
 * consult the repository of the project.
 *
 * @link http://github.com/seppo0010/phpiredis
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class PhpiredisConnection extends ConnectionBase
{
    private $_reader;

    /**
     * {@inheritdoc}
     */
    public function __construct(IConnectionParameters $parameters)
    {
        if (!function_exists('socket_create')) {
            throw new ClientException(
                'The socket extension must be loaded in order to be able to ' .
                'use this connection class'
            );
        }

        parent::__construct($parameters);
    }

    /**
     * Disconnects from the server and destroys the underlying resource and the
     * protocol reader resource when PHP's garbage collector kicks in.
     */
    public function __destruct()
    {
        phpiredis_reader_destroy($this->_reader);

        parent::__destruct();
    }

    /**
     * {@inheritdoc}
     */
    protected function checkParameters(IConnectionParameters $parameters)
    {
        if ($parameters->isSetByUser('iterable_multibulk')) {
            $this->onInvalidOption('iterable_multibulk', $parameters);
        }
        if ($parameters->isSetByUser('connection_persistent')) {
            $this->onInvalidOption('connection_persistent', $parameters);
        }

        return parent::checkParameters($parameters);
    }

    /**
     * Initializes the protocol reader resource.
     *
     * @param Boolean $throw_errors Specify if Redis errors throw exceptions.
     */
    private function initializeReader($throw_errors = true)
    {
        if (!function_exists('phpiredis_reader_create')) {
            throw new ClientException(
                'The phpiredis extension must be loaded in order to be able to ' .
                'use this connection class'
            );
        }

        $reader = phpiredis_reader_create();

        phpiredis_reader_set_status_handler($reader, $this->getStatusHandler());
        phpiredis_reader_set_error_handler($reader, $this->getErrorHandler($throw_errors));

        $this->_reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeProtocol(IConnectionParameters $parameters)
    {
        $this->initializeReader($parameters->throw_errors);
    }

    /**
     * Gets the handler used by the protocol reader to handle status replies.
     *
     * @return \Closure
     */
    private function getStatusHandler()
    {
        return function($payload) {
            switch ($payload) {
                case 'OK':
                    return true;

                case 'QUEUED':
                    return new ResponseQueued();

                default:
                    return $payload;
            }
        };
    }

    /**
     * Gets the handler used by the protocol reader to handle Redis errors.
     *
     * @param Boolean $throw_errors Specify if Redis errors throw exceptions.
     * @return \Closure
     */
    private function getErrorHandler($throwErrors = true)
    {
        if ($throwErrors) {
            return function($errorMessage) {
                throw new ServerException($errorMessage);
            };
        }

        return function($errorMessage) {
            return new ResponseError($errorMessage);
        };
    }

    /**
     * Helper method used to throw exceptions on socket errors.
     */
    private function emitSocketError()
    {
        $errno  = socket_last_error();
        $errstr = socket_strerror($errno);

        $this->disconnect();

        $this->onConnectionError(trim($errstr), $errno);
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource()
    {
        $parameters = $this->_params;

        $initializer = array($this, "{$parameters->scheme}SocketInitializer");
        $socket = call_user_func($initializer, $parameters);

        $this->setSocketOptions($socket, $parameters);

        return $socket;
    }

    /**
     * Initializes a TCP socket resource.
     *
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     * @return resource
     */
    private function tcpSocketInitializer(IConnectionParameters $parameters)
    {
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!is_resource($socket)) {
            $this->emitSocketError();
        }

        return $socket;
    }

    /**
     * Initializes a UNIX socket resource.
     *
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     * @return resource
     */
    private function unixSocketInitializer(IConnectionParameters $parameters)
    {
        $socket = @socket_create(AF_UNIX, SOCK_STREAM, 0);

        if (!is_resource($socket)) {
            $this->emitSocketError();
        }

        return $socket;
    }

    /**
     * Sets options on the socket resource from the connection parameters.
     *
     * @param resource $socket Socket resource.
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     */
    private function setSocketOptions($socket, IConnectionParameters $parameters)
    {
        if ($parameters->scheme !== 'tcp') {
            return;
        }

        if (!socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1)) {
            $this->emitSocketError();
        }

        if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
            $this->emitSocketError();
        }

        if (isset($parameters->read_write_timeout)) {
            $rwtimeout = $parameters->read_write_timeout;
            $timeoutSec = floor($rwtimeout);
            $timeoutUsec = ($rwtimeout - $timeoutSec) * 1000000;

            $timeout = array(
                'sec' => $timeoutSec,
                'usec' => $timeoutUsec,
            );

            if (!socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $timeout)) {
                $this->emitSocketError();
            }

            if (!socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout)) {
                $this->emitSocketError();
            }
        }
    }

    /**
     * Gets the address from the connection parameters.
     *
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     * @return string
     */
    private function getAddress(IConnectionParameters $parameters)
    {
        if ($parameters->scheme === 'unix') {
            return $parameters->path;
        }

        $host = $parameters->host;

        if (ip2long($host) === false) {
            if (($address = gethostbyname($host)) === $host) {
                $this->onConnectionError("Cannot resolve the address of $host");
            }
            return $address;
        }

        return $host;
    }

    /**
     * Opens the actual connection to the server with a timeout.
     *
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     * @return string
     */
    private function connectWithTimeout(IConnectionParameters $parameters) {
        $host = self::getAddress($parameters);
        $socket = $this->getResource();

        socket_set_nonblock($socket);

        if (@socket_connect($socket, $host, $parameters->port) === false) {
            $error = socket_last_error();
            if ($error != SOCKET_EINPROGRESS && $error != SOCKET_EALREADY) {
                $this->emitSocketError();
            }
        }

        socket_set_block($socket);

        $null = null;
        $selectable = array($socket);

        $timeout = $parameters->connection_timeout;
        $timeoutSecs = floor($timeout);
        $timeoutUSecs = ($timeout - $timeoutSecs) * 1000000;

        $selected = socket_select($selectable, $selectable, $null, $timeoutSecs, $timeoutUSecs);

        if ($selected === 2) {
            $this->onConnectionError('Connection refused', SOCKET_ECONNREFUSED);
        }
        if ($selected === 0) {
            $this->onConnectionError('Connection timed out', SOCKET_ETIMEDOUT);
        }
        if ($selected === false) {
            $this->emitSocketError();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        parent::connect();

        $this->connectWithTimeout($this->_params);
        if (count($this->_initCmds) > 0) {
            $this->sendInitializationCommands();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            socket_close($this->getResource());

            parent::disconnect();
        }
    }

    /**
     * Sends the initialization commands to Redis when the connection is opened.
     */
    private function sendInitializationCommands()
    {
        foreach ($this->_initCmds as $command) {
            $this->writeCommand($command);
        }
        foreach ($this->_initCmds as $command) {
            $this->readResponse($command);
        }
    }

    /**
     * {@inheritdoc}
     */
    private function write($buffer)
    {
        $socket = $this->getResource();

        while (($length = strlen($buffer)) > 0) {
            $written = socket_write($socket, $buffer, $length);

            if ($length === $written) {
                return;
            }
            if ($written === false) {
                $this->onConnectionError('Error while writing bytes to the server');
            }

            $buffer = substr($buffer, $written);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $socket = $this->getResource();
        $reader = $this->_reader;

        while (($state = phpiredis_reader_get_state($reader)) === PHPIREDIS_READER_STATE_INCOMPLETE) {
            if (@socket_recv($socket, $buffer, 4096, 0) === false || $buffer === '') {
                $this->emitSocketError();
            }

            phpiredis_reader_feed($reader, $buffer);
        }

        if ($state === PHPIREDIS_READER_STATE_COMPLETE) {
            return phpiredis_reader_get_reply($reader);
        }
        else {
            $this->onProtocolError(phpiredis_reader_get_error($reader));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeCommand(ICommand $command)
    {
        $cmdargs = $command->getArguments();
        array_unshift($cmdargs, $command->getId());
        $this->write(phpiredis_format_command($cmdargs));
    }
}

/**
 * Connection abstraction to Redis servers based on PHP's stream that uses an
 * external protocol processor defining the protocol used for the communication.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ComposableStreamConnection extends StreamConnection implements IConnectionComposable
{
    private $_protocol;

    /**
     * @param IConnectionParameters $parameters Parameters used to initialize the connection.
     * @param IProtocolProcessor $protocol A protocol processor.
     */
    public function __construct(IConnectionParameters $parameters, IProtocolProcessor $protocol = null)
    {
        $this->setProtocol($protocol ?: new TextProtocol());

        parent::__construct($parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeProtocol(IConnectionParameters $parameters)
    {
        $this->_protocol->setOption('throw_errors', $parameters->throw_errors);
        $this->_protocol->setOption('iterable_multibulk', $parameters->iterable_multibulk);
    }

    /**
     * {@inheritdoc}
     */
    public function setProtocol(IProtocolProcessor $protocol)
    {
        if ($protocol === null) {
            throw new \InvalidArgumentException("The protocol instance cannot be a null value");
        }
        $this->_protocol = $protocol;
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocol()
    {
        return $this->_protocol;
    }

    /**
     * {@inheritdoc}
     */
    public function writeBytes($buffer)
    {
        parent::writeBytes($buffer);
    }

    /**
     * {@inheritdoc}
     */
    public function readBytes($length)
    {
        if ($length <= 0) {
            throw new \InvalidArgumentException('Length parameter must be greater than 0');
        }

        $value  = '';
        $socket = $this->getResource();

        do {
            $chunk = fread($socket, $length);
            if ($chunk === false || $chunk === '') {
                $this->onConnectionError('Error while reading bytes from the server');
            }
            $value .= $chunk;
        }
        while (($length -= strlen($chunk)) > 0);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function readLine()
    {
        $value  = '';
        $socket = $this->getResource();

        do {
            $chunk = fgets($socket);
            if ($chunk === false || $chunk === '') {
                $this->onConnectionError('Error while reading line from the server');
            }
            $value .= $chunk;
        }
        while (substr($value, -2) !== "\r\n");

        return substr($value, 0, -2);
    }

    /**
     * {@inheritdoc}
     */
    public function writeCommand(ICommand $command)
    {
        $this->_protocol->write($this, $command);
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        return $this->_protocol->read($this);
    }
}

/**
 * Abstraction for a cluster of aggregated connections to various Redis servers
 * implementing client-side sharding based on pluggable distribution strategies.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class PredisCluster implements IConnectionCluster, \IteratorAggregate
{
    private $_pool;
    private $_distributor;

    /**
     * @param IDistributionStrategy $distributor Distribution strategy used by the cluster.
     */
    public function __construct(IDistributionStrategy $distributor = null)
    {
        $this->_pool = array();
        $this->_distributor = $distributor ?: new HashRing();
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        foreach ($this->_pool as $connection) {
            if ($connection->isConnected()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        foreach ($this->_pool as $connection) {
            $connection->connect();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        foreach ($this->_pool as $connection) {
            $connection->disconnect();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(IConnectionSingle $connection)
    {
        $parameters = $connection->getParameters();

        if (isset($parameters->alias)) {
            $this->_pool[$parameters->alias] = $connection;
        }
        else {
            $this->_pool[] = $connection;
        }

        $this->_distributor->add($connection, $parameters->weight);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(ICommand $command)
    {
        $cmdHash = $command->getHash($this->_distributor);

        if (isset($cmdHash)) {
            return $this->_distributor->get($cmdHash);
        }

        throw new ClientException(
            sprintf("Cannot send '%s' commands to a cluster of connections", $command->getId())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionById($id = null)
    {
        $alias = $id ?: 0;

        return isset($this->_pool[$alias]) ? $this->_pool[$alias] : null;
    }


    /**
     * Retrieves a connection instance from the cluster using a key.
     *
     * @param string $key Key of a Redis value.
     * @return IConnectionSingle
     */
    public function getConnectionByKey($key)
    {
        $hashablePart = Helpers::getKeyHashablePart($key);
        $keyHash = $this->_distributor->generateKey($hashablePart);

        return $this->_distributor->get($keyHash);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_pool);
    }

    /**
     * {@inheritdoc}
     */
    public function writeCommand(ICommand $command)
    {
        $this->getConnection($command)->writeCommand($command);
    }

    /**
     * {@inheritdoc}
     */
    public function readResponse(ICommand $command)
    {
        return $this->getConnection($command)->readResponse($command);
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand(ICommand $command)
    {
        return $this->getConnection($command)->executeCommand($command);
    }
}

/* --------------------------------------------------------------------------- */

namespace Predis\Protocol;

use Predis\Commands\ICommand;
use Predis\Network\IConnectionComposable;
use Predis\CommunicationException;

/**
 * Interface that defines an handler able to parse a reply.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IResponseHandler
{
    /**
     * Parses a type of reply returned by Redis and reads more data from the
     * connection if needed.
     *
     * @param IConnectionComposable $connection Connection to Redis.
     * @param string $payload Initial payload of the reply.
     * @return mixed
     */
    function handle(IConnectionComposable $connection, $payload);
}

/**
 * Interface that defines a response reader able to parse replies returned by
 * Redis and deserialize them to PHP objects.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IResponseReader
{
    /**
     * Reads replies from a connection to Redis and deserializes them.
     *
     * @param IConnectionComposable $connection Connection to Redis.
     * @return mixed
     */
    public function read(IConnectionComposable $connection);
}

/**
 * Interface that defines a protocol processor that serializes Redis commands
 * and parses replies returned by the server to PHP objects.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IProtocolProcessor extends IResponseReader
{
    /**
     * Writes a Redis command on the specified connection.
     *
     * @param IConnectionComposable $connection Connection to Redis.
     * @param ICommand $command Redis command.
     */
    public function write(IConnectionComposable $connection, ICommand $command);

    /**
     * Sets the options for the protocol processor.
     *
     * @param string $option Name of the option.
     * @param mixed $value Value of the option.
     */
    public function setOption($option, $value);
}

/**
 * Interface that defines a custom serializer for Redis commands.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface ICommandSerializer
{
    /**
     * Serializes a Redis command.
     *
     * @param ICommand $command Redis command.
     * @return string
     */
    public function serialize(ICommand $command);
}

/**
 * Interface that defines a customizable protocol processor that serializes
 * Redis commands and parses replies returned by the server to PHP objects
 * using a pluggable set of classes defining the underlying wire protocol.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IComposableProtocolProcessor extends IProtocolProcessor
{
    /**
     * Sets the command serializer to be used by the protocol processor.
     *
     * @param ICommandSerializer $serializer Command serializer.
     */
    public function setSerializer(ICommandSerializer $serializer);

    /**
     * Returns the command serializer used by the protocol processor.
     *
     * @return ICommandSerializer
     */
    public function getSerializer();

    /**
     * Sets the response reader to be used by the protocol processor.
     *
     * @param IResponseReader $reader Response reader.
     */
    public function setReader(IResponseReader $reader);

    /**
     * Returns the response reader used by the protocol processor.
     *
     * @return IResponseReader
     */
    public function getReader();
}

/**
 * Exception class that identifies errors encountered while
 * handling the Redis wire protocol.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ProtocolException extends CommunicationException
{
}

/* --------------------------------------------------------------------------- */

namespace Predis\Profiles;

use Predis\ClientException;
use Predis\Commands\Processors\ICommandProcessor;
use Predis\Commands\Processors\IProcessingSupport;

/**
 * A server profile defines features and commands supported by certain
 * versions of Redis. Instances of Predis\Client should use a server
 * profile matching the version of Redis in use.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IServerProfile
{
    /**
     * Gets a profile version corresponding to a Redis version.
     *
     * @return string
     */
    public function getVersion();

    /**
     * Checks if the profile supports the specified command.
     *
     * @param string $command Command ID.
     * @return Boolean
     */
    public function supportsCommand($command);

    /**
     * Checks if the profile supports the specified list of commands.
     *
     * @param array $commands List of command IDs.
     * @return string
     */
    public function supportsCommands(Array $commands);

    /**
     * Creates a new command instance.
     *
     * @param string $method Command ID.
     * @param array $arguments Arguments for the command.
     * @return Predis\Commands\ICommand
     */
    public function createCommand($method, $arguments = array());
}

/**
 * Base class that implements common functionalities of server profiles.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
abstract class ServerProfile implements IServerProfile, IProcessingSupport
{
    private static $_profiles;

    private $_registeredCommands;
    private $_processor;

    /**
     *
     */
    public function __construct()
    {
        $this->_registeredCommands = $this->getSupportedCommands();
    }

    /**
     * Returns a map of all the commands supported by the profile and their
     * actual PHP classes.
     *
     * @return array
     */
    protected abstract function getSupportedCommands();

    /**
     * Returns the default server profile.
     *
     * @return IServerProfile
     */
    public static function getDefault()
    {
        return self::get('default');
    }

    /**
     * Returns the development server profile.
     *
     * @return IServerProfile
     */
    public static function getDevelopment()
    {
        return self::get('dev');
    }

    /**
     * Returns a map of all the server profiles supported by default and their
     * actual PHP classes.
     *
     * @return array
     */
    private static function getDefaultProfiles()
    {
        return array(
            '1.2'     => '\Predis\Profiles\ServerVersion12',
            '2.0'     => '\Predis\Profiles\ServerVersion20',
            '2.2'     => '\Predis\Profiles\ServerVersion22',
            '2.4'     => '\Predis\Profiles\ServerVersion24',
            'default' => '\Predis\Profiles\ServerVersion24',
            'dev'     => '\Predis\Profiles\ServerVersionNext',
        );
    }

    /**
     * Registers a new server profile.
     *
     * @param string $alias Profile version or alias.
     * @param string $profileClass FQN of a class implementing Predis\Profiles\IServerProfile.
     */
    public static function define($alias, $profileClass)
    {
        if (!isset(self::$_profiles)) {
            self::$_profiles = self::getDefaultProfiles();
        }

        $profileReflection = new \ReflectionClass($profileClass);

        if (!$profileReflection->isSubclassOf('\Predis\Profiles\IServerProfile')) {
            throw new ClientException(
                "Cannot register '$profileClass' as it is not a valid profile class"
            );
        }

        self::$_profiles[$alias] = $profileClass;
    }

    /**
     * Returns the specified server profile.
     *
     * @param string $version Profile version or alias.
     * @return IServerProfile
     */
    public static function get($version)
    {
        if (!isset(self::$_profiles)) {
            self::$_profiles = self::getDefaultProfiles();
        }
        if (!isset(self::$_profiles[$version])) {
            throw new ClientException("Unknown server profile: $version");
        }

        $profile = self::$_profiles[$version];

        return new $profile();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCommands(Array $commands)
    {
        foreach ($commands as $command) {
            if ($this->supportsCommand($command) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCommand($command)
    {
        return isset($this->_registeredCommands[strtolower($command)]);
    }

    /**
     * {@inheritdoc}
     */
    public function createCommand($method, $arguments = array())
    {
        $method = strtolower($method);
        if (!isset($this->_registeredCommands[$method])) {
            throw new ClientException("'$method' is not a registered Redis command");
        }

        $commandClass = $this->_registeredCommands[$method];
        $command = new $commandClass();
        $command->setArguments($arguments);

        if (isset($this->_processor)) {
            $this->_processor->process($command);
        }

        return $command;
    }

    /**
     * Defines new commands in the server profile.
     *
     * @param array $commands Named list of command IDs and their classes.
     */
    public function defineCommands(Array $commands)
    {
        foreach ($commands as $alias => $command) {
            $this->defineCommand($alias, $command);
        }
    }

    /**
     * Defines a new commands in the server profile.
     *
     * @param string $alias Command ID.
     * @param string $command FQN of a class implementing Predis\Commands\ICommand.
     */
    public function defineCommand($alias, $command)
    {
        $commandReflection = new \ReflectionClass($command);
        if (!$commandReflection->isSubclassOf('\Predis\Commands\ICommand')) {
            throw new ClientException("Cannot register '$command' as it is not a valid Redis command");
        }
        $this->_registeredCommands[strtolower($alias)] = $command;
    }

    /**
     * {@inheritdoc}
     */
    public function setProcessor(ICommandProcessor $processor)
    {
        if (!isset($processor)) {
            unset($this->_processor);
            return;
        }
        $this->_processor = $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessor()
    {
        return $this->_processor;
    }

    /**
     * Returns the version of server profile as its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getVersion();
    }
}

/**
 * Server profile for Redis v2.4.x.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerVersion24 extends ServerProfile
{
    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.4';
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedCommands()
    {
        return array(
            /* ---------------- Redis 1.2 ---------------- */

            /* commands operating on the key space */
            'exists'                    => '\Predis\Commands\KeyExists',
            'del'                       => '\Predis\Commands\KeyDelete',
            'type'                      => '\Predis\Commands\KeyType',
            'keys'                      => '\Predis\Commands\KeyKeys',
            'randomkey'                 => '\Predis\Commands\KeyRandom',
            'rename'                    => '\Predis\Commands\KeyRename',
            'renamenx'                  => '\Predis\Commands\KeyRenamePreserve',
            'expire'                    => '\Predis\Commands\KeyExpire',
            'expireat'                  => '\Predis\Commands\KeyExpireAt',
            'ttl'                       => '\Predis\Commands\KeyTimeToLive',
            'move'                      => '\Predis\Commands\KeyMove',
            'sort'                      => '\Predis\Commands\KeySort',

            /* commands operating on string values */
            'set'                       => '\Predis\Commands\StringSet',
            'setnx'                     => '\Predis\Commands\StringSetPreserve',
            'mset'                      => '\Predis\Commands\StringSetMultiple',
            'msetnx'                    => '\Predis\Commands\StringSetMultiplePreserve',
            'get'                       => '\Predis\Commands\StringGet',
            'mget'                      => '\Predis\Commands\StringGetMultiple',
            'getset'                    => '\Predis\Commands\StringGetSet',
            'incr'                      => '\Predis\Commands\StringIncrement',
            'incrby'                    => '\Predis\Commands\StringIncrementBy',
            'decr'                      => '\Predis\Commands\StringDecrement',
            'decrby'                    => '\Predis\Commands\StringDecrementBy',

            /* commands operating on lists */
            'rpush'                     => '\Predis\Commands\ListPushTail',
            'lpush'                     => '\Predis\Commands\ListPushHead',
            'llen'                      => '\Predis\Commands\ListLength',
            'lrange'                    => '\Predis\Commands\ListRange',
            'ltrim'                     => '\Predis\Commands\ListTrim',
            'lindex'                    => '\Predis\Commands\ListIndex',
            'lset'                      => '\Predis\Commands\ListSet',
            'lrem'                      => '\Predis\Commands\ListRemove',
            'lpop'                      => '\Predis\Commands\ListPopFirst',
            'rpop'                      => '\Predis\Commands\ListPopLast',
            'rpoplpush'                 => '\Predis\Commands\ListPopLastPushHead',

            /* commands operating on sets */
            'sadd'                      => '\Predis\Commands\SetAdd',
            'srem'                      => '\Predis\Commands\SetRemove',
            'spop'                      => '\Predis\Commands\SetPop',
            'smove'                     => '\Predis\Commands\SetMove',
            'scard'                     => '\Predis\Commands\SetCardinality',
            'sismember'                 => '\Predis\Commands\SetIsMember',
            'sinter'                    => '\Predis\Commands\SetIntersection',
            'sinterstore'               => '\Predis\Commands\SetIntersectionStore',
            'sunion'                    => '\Predis\Commands\SetUnion',
            'sunionstore'               => '\Predis\Commands\SetUnionStore',
            'sdiff'                     => '\Predis\Commands\SetDifference',
            'sdiffstore'                => '\Predis\Commands\SetDifferenceStore',
            'smembers'                  => '\Predis\Commands\SetMembers',
            'srandmember'               => '\Predis\Commands\SetRandomMember',

            /* commands operating on sorted sets */
            'zadd'                      => '\Predis\Commands\ZSetAdd',
            'zincrby'                   => '\Predis\Commands\ZSetIncrementBy',
            'zrem'                      => '\Predis\Commands\ZSetRemove',
            'zrange'                    => '\Predis\Commands\ZSetRange',
            'zrevrange'                 => '\Predis\Commands\ZSetReverseRange',
            'zrangebyscore'             => '\Predis\Commands\ZSetRangeByScore',
            'zcard'                     => '\Predis\Commands\ZSetCardinality',
            'zscore'                    => '\Predis\Commands\ZSetScore',
            'zremrangebyscore'          => '\Predis\Commands\ZSetRemoveRangeByScore',

            /* connection related commands */
            'ping'                      => '\Predis\Commands\ConnectionPing',
            'auth'                      => '\Predis\Commands\ConnectionAuth',
            'select'                    => '\Predis\Commands\ConnectionSelect',
            'echo'                      => '\Predis\Commands\ConnectionEcho',
            'quit'                      => '\Predis\Commands\ConnectionQuit',

            /* remote server control commands */
            'info'                      => '\Predis\Commands\ServerInfo',
            'slaveof'                   => '\Predis\Commands\ServerSlaveOf',
            'monitor'                   => '\Predis\Commands\ServerMonitor',
            'dbsize'                    => '\Predis\Commands\ServerDatabaseSize',
            'flushdb'                   => '\Predis\Commands\ServerFlushDatabase',
            'flushall'                  => '\Predis\Commands\ServerFlushAll',
            'save'                      => '\Predis\Commands\ServerSave',
            'bgsave'                    => '\Predis\Commands\ServerBackgroundSave',
            'lastsave'                  => '\Predis\Commands\ServerLastSave',
            'shutdown'                  => '\Predis\Commands\ServerShutdown',
            'bgrewriteaof'              => '\Predis\Commands\ServerBackgroundRewriteAOF',


            /* ---------------- Redis 2.0 ---------------- */

            /* commands operating on string values */
            'setex'                     => '\Predis\Commands\StringSetExpire',
            'append'                    => '\Predis\Commands\StringAppend',
            'substr'                    => '\Predis\Commands\StringSubstr',

            /* commands operating on lists */
            'blpop'                     => '\Predis\Commands\ListPopFirstBlocking',
            'brpop'                     => '\Predis\Commands\ListPopLastBlocking',

            /* commands operating on sorted sets */
            'zunionstore'               => '\Predis\Commands\ZSetUnionStore',
            'zinterstore'               => '\Predis\Commands\ZSetIntersectionStore',
            'zcount'                    => '\Predis\Commands\ZSetCount',
            'zrank'                     => '\Predis\Commands\ZSetRank',
            'zrevrank'                  => '\Predis\Commands\ZSetReverseRank',
            'zremrangebyrank'           => '\Predis\Commands\ZSetRemoveRangeByRank',

            /* commands operating on hashes */
            'hset'                      => '\Predis\Commands\HashSet',
            'hsetnx'                    => '\Predis\Commands\HashSetPreserve',
            'hmset'                     => '\Predis\Commands\HashSetMultiple',
            'hincrby'                   => '\Predis\Commands\HashIncrementBy',
            'hget'                      => '\Predis\Commands\HashGet',
            'hmget'                     => '\Predis\Commands\HashGetMultiple',
            'hdel'                      => '\Predis\Commands\HashDelete',
            'hexists'                   => '\Predis\Commands\HashExists',
            'hlen'                      => '\Predis\Commands\HashLength',
            'hkeys'                     => '\Predis\Commands\HashKeys',
            'hvals'                     => '\Predis\Commands\HashValues',
            'hgetall'                   => '\Predis\Commands\HashGetAll',

            /* transactions */
            'multi'                     => '\Predis\Commands\TransactionMulti',
            'exec'                      => '\Predis\Commands\TransactionExec',
            'discard'                   => '\Predis\Commands\TransactionDiscard',

            /* publish - subscribe */
            'subscribe'                 => '\Predis\Commands\PubSubSubscribe',
            'unsubscribe'               => '\Predis\Commands\PubSubUnsubscribe',
            'psubscribe'                => '\Predis\Commands\PubSubSubscribeByPattern',
            'punsubscribe'              => '\Predis\Commands\PubSubUnsubscribeByPattern',
            'publish'                   => '\Predis\Commands\PubSubPublish',

            /* remote server control commands */
            'config'                    => '\Predis\Commands\ServerConfig',


            /* ---------------- Redis 2.2 ---------------- */

            /* commands operating on the key space */
            'persist'                   => '\Predis\Commands\KeyPersist',

            /* commands operating on string values */
            'strlen'                    => '\Predis\Commands\StringStrlen',
            'setrange'                  => '\Predis\Commands\StringSetRange',
            'getrange'                  => '\Predis\Commands\StringGetRange',
            'setbit'                    => '\Predis\Commands\StringSetBit',
            'getbit'                    => '\Predis\Commands\StringGetBit',

            /* commands operating on lists */
            'rpushx'                    => '\Predis\Commands\ListPushTailX',
            'lpushx'                    => '\Predis\Commands\ListPushHeadX',
            'linsert'                   => '\Predis\Commands\ListInsert',
            'brpoplpush'                => '\Predis\Commands\ListPopLastPushHeadBlocking',

            /* commands operating on sorted sets */
            'zrevrangebyscore'          => '\Predis\Commands\ZSetReverseRangeByScore',

            /* transactions */
            'watch'                     => '\Predis\Commands\TransactionWatch',
            'unwatch'                   => '\Predis\Commands\TransactionUnwatch',

            /* remote server control commands */
            'object'                    => '\Predis\Commands\ServerObject',


            /* ---------------- Redis 2.4 ---------------- */

            /* remote server control commands */
            'client'                    => '\Predis\Commands\ServerClient',
        );
    }
}

/**
 * Server profile for the current development version of Redis.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerVersionNext extends ServerVersion24
{
    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.6';
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedCommands()
    {
        return array_merge(parent::getSupportedCommands(), array(
            'info'                      => '\Predis\Commands\ServerInfoV26x',
            'eval'                      => '\Predis\Commands\ServerEval',
            'evalsha'                   => '\Predis\Commands\ServerEvalSHA',
        ));
    }
}

/**
 * Server profile for Redis v2.0.x.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerVersion20 extends ServerProfile
{
    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedCommands()
    {
        return array(
            /* ---------------- Redis 1.2 ---------------- */

            /* commands operating on the key space */
            'exists'                    => '\Predis\Commands\KeyExists',
            'del'                       => '\Predis\Commands\KeyDelete',
            'type'                      => '\Predis\Commands\KeyType',
            'keys'                      => '\Predis\Commands\KeyKeys',
            'randomkey'                 => '\Predis\Commands\KeyRandom',
            'rename'                    => '\Predis\Commands\KeyRename',
            'renamenx'                  => '\Predis\Commands\KeyRenamePreserve',
            'expire'                    => '\Predis\Commands\KeyExpire',
            'expireat'                  => '\Predis\Commands\KeyExpireAt',
            'ttl'                       => '\Predis\Commands\KeyTimeToLive',
            'move'                      => '\Predis\Commands\KeyMove',
            'sort'                      => '\Predis\Commands\KeySort',

            /* commands operating on string values */
            'set'                       => '\Predis\Commands\StringSet',
            'setnx'                     => '\Predis\Commands\StringSetPreserve',
            'mset'                      => '\Predis\Commands\StringSetMultiple',
            'msetnx'                    => '\Predis\Commands\StringSetMultiplePreserve',
            'get'                       => '\Predis\Commands\StringGet',
            'mget'                      => '\Predis\Commands\StringGetMultiple',
            'getset'                    => '\Predis\Commands\StringGetSet',
            'incr'                      => '\Predis\Commands\StringIncrement',
            'incrby'                    => '\Predis\Commands\StringIncrementBy',
            'decr'                      => '\Predis\Commands\StringDecrement',
            'decrby'                    => '\Predis\Commands\StringDecrementBy',

            /* commands operating on lists */
            'rpush'                     => '\Predis\Commands\ListPushTail',
            'lpush'                     => '\Predis\Commands\ListPushHead',
            'llen'                      => '\Predis\Commands\ListLength',
            'lrange'                    => '\Predis\Commands\ListRange',
            'ltrim'                     => '\Predis\Commands\ListTrim',
            'lindex'                    => '\Predis\Commands\ListIndex',
            'lset'                      => '\Predis\Commands\ListSet',
            'lrem'                      => '\Predis\Commands\ListRemove',
            'lpop'                      => '\Predis\Commands\ListPopFirst',
            'rpop'                      => '\Predis\Commands\ListPopLast',
            'rpoplpush'                 => '\Predis\Commands\ListPopLastPushHead',

            /* commands operating on sets */
            'sadd'                      => '\Predis\Commands\SetAdd',
            'srem'                      => '\Predis\Commands\SetRemove',
            'spop'                      => '\Predis\Commands\SetPop',
            'smove'                     => '\Predis\Commands\SetMove',
            'scard'                     => '\Predis\Commands\SetCardinality',
            'sismember'                 => '\Predis\Commands\SetIsMember',
            'sinter'                    => '\Predis\Commands\SetIntersection',
            'sinterstore'               => '\Predis\Commands\SetIntersectionStore',
            'sunion'                    => '\Predis\Commands\SetUnion',
            'sunionstore'               => '\Predis\Commands\SetUnionStore',
            'sdiff'                     => '\Predis\Commands\SetDifference',
            'sdiffstore'                => '\Predis\Commands\SetDifferenceStore',
            'smembers'                  => '\Predis\Commands\SetMembers',
            'srandmember'               => '\Predis\Commands\SetRandomMember',

            /* commands operating on sorted sets */
            'zadd'                      => '\Predis\Commands\ZSetAdd',
            'zincrby'                   => '\Predis\Commands\ZSetIncrementBy',
            'zrem'                      => '\Predis\Commands\ZSetRemove',
            'zrange'                    => '\Predis\Commands\ZSetRange',
            'zrevrange'                 => '\Predis\Commands\ZSetReverseRange',
            'zrangebyscore'             => '\Predis\Commands\ZSetRangeByScore',
            'zcard'                     => '\Predis\Commands\ZSetCardinality',
            'zscore'                    => '\Predis\Commands\ZSetScore',
            'zremrangebyscore'          => '\Predis\Commands\ZSetRemoveRangeByScore',

            /* connection related commands */
            'ping'                      => '\Predis\Commands\ConnectionPing',
            'auth'                      => '\Predis\Commands\ConnectionAuth',
            'select'                    => '\Predis\Commands\ConnectionSelect',
            'echo'                      => '\Predis\Commands\ConnectionEcho',
            'quit'                      => '\Predis\Commands\ConnectionQuit',

            /* remote server control commands */
            'info'                      => '\Predis\Commands\ServerInfo',
            'slaveof'                   => '\Predis\Commands\ServerSlaveOf',
            'monitor'                   => '\Predis\Commands\ServerMonitor',
            'dbsize'                    => '\Predis\Commands\ServerDatabaseSize',
            'flushdb'                   => '\Predis\Commands\ServerFlushDatabase',
            'flushall'                  => '\Predis\Commands\ServerFlushAll',
            'save'                      => '\Predis\Commands\ServerSave',
            'bgsave'                    => '\Predis\Commands\ServerBackgroundSave',
            'lastsave'                  => '\Predis\Commands\ServerLastSave',
            'shutdown'                  => '\Predis\Commands\ServerShutdown',
            'bgrewriteaof'              => '\Predis\Commands\ServerBackgroundRewriteAOF',


            /* ---------------- Redis 2.0 ---------------- */

            /* commands operating on string values */
            'setex'                     => '\Predis\Commands\StringSetExpire',
            'append'                    => '\Predis\Commands\StringAppend',
            'substr'                    => '\Predis\Commands\StringSubstr',

            /* commands operating on lists */
            'blpop'                     => '\Predis\Commands\ListPopFirstBlocking',
            'brpop'                     => '\Predis\Commands\ListPopLastBlocking',

            /* commands operating on sorted sets */
            'zunionstore'               => '\Predis\Commands\ZSetUnionStore',
            'zinterstore'               => '\Predis\Commands\ZSetIntersectionStore',
            'zcount'                    => '\Predis\Commands\ZSetCount',
            'zrank'                     => '\Predis\Commands\ZSetRank',
            'zrevrank'                  => '\Predis\Commands\ZSetReverseRank',
            'zremrangebyrank'           => '\Predis\Commands\ZSetRemoveRangeByRank',

            /* commands operating on hashes */
            'hset'                      => '\Predis\Commands\HashSet',
            'hsetnx'                    => '\Predis\Commands\HashSetPreserve',
            'hmset'                     => '\Predis\Commands\HashSetMultiple',
            'hincrby'                   => '\Predis\Commands\HashIncrementBy',
            'hget'                      => '\Predis\Commands\HashGet',
            'hmget'                     => '\Predis\Commands\HashGetMultiple',
            'hdel'                      => '\Predis\Commands\HashDelete',
            'hexists'                   => '\Predis\Commands\HashExists',
            'hlen'                      => '\Predis\Commands\HashLength',
            'hkeys'                     => '\Predis\Commands\HashKeys',
            'hvals'                     => '\Predis\Commands\HashValues',
            'hgetall'                   => '\Predis\Commands\HashGetAll',

            /* transactions */
            'multi'                     => '\Predis\Commands\TransactionMulti',
            'exec'                      => '\Predis\Commands\TransactionExec',
            'discard'                   => '\Predis\Commands\TransactionDiscard',

            /* publish - subscribe */
            'subscribe'                 => '\Predis\Commands\PubSubSubscribe',
            'unsubscribe'               => '\Predis\Commands\PubSubUnsubscribe',
            'psubscribe'                => '\Predis\Commands\PubSubSubscribeByPattern',
            'punsubscribe'              => '\Predis\Commands\PubSubUnsubscribeByPattern',
            'publish'                   => '\Predis\Commands\PubSubPublish',

            /* remote server control commands */
            'config'                    => '\Predis\Commands\ServerConfig',
        );
    }
}

/**
 * Server profile for Redis v1.2.x.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerVersion12 extends ServerProfile
{
    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '1.2';
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedCommands()
    {
        return array(
            /* ---------------- Redis 1.2 ---------------- */

            /* commands operating on the key space */
            'exists'                    => '\Predis\Commands\KeyExists',
            'del'                       => '\Predis\Commands\KeyDelete',
            'type'                      => '\Predis\Commands\KeyType',
            'keys'                      => '\Predis\Commands\KeyKeysV12x',
            'randomkey'                 => '\Predis\Commands\KeyRandom',
            'rename'                    => '\Predis\Commands\KeyRename',
            'renamenx'                  => '\Predis\Commands\KeyRenamePreserve',
            'expire'                    => '\Predis\Commands\KeyExpire',
            'expireat'                  => '\Predis\Commands\KeyExpireAt',
            'ttl'                       => '\Predis\Commands\KeyTimeToLive',
            'move'                      => '\Predis\Commands\KeyMove',
            'sort'                      => '\Predis\Commands\KeySort',

            /* commands operating on string values */
            'set'                       => '\Predis\Commands\StringSet',
            'setnx'                     => '\Predis\Commands\StringSetPreserve',
            'mset'                      => '\Predis\Commands\StringSetMultiple',
            'msetnx'                    => '\Predis\Commands\StringSetMultiplePreserve',
            'get'                       => '\Predis\Commands\StringGet',
            'mget'                      => '\Predis\Commands\StringGetMultiple',
            'getset'                    => '\Predis\Commands\StringGetSet',
            'incr'                      => '\Predis\Commands\StringIncrement',
            'incrby'                    => '\Predis\Commands\StringIncrementBy',
            'decr'                      => '\Predis\Commands\StringDecrement',
            'decrby'                    => '\Predis\Commands\StringDecrementBy',

            /* commands operating on lists */
            'rpush'                     => '\Predis\Commands\ListPushTail',
            'lpush'                     => '\Predis\Commands\ListPushHead',
            'llen'                      => '\Predis\Commands\ListLength',
            'lrange'                    => '\Predis\Commands\ListRange',
            'ltrim'                     => '\Predis\Commands\ListTrim',
            'lindex'                    => '\Predis\Commands\ListIndex',
            'lset'                      => '\Predis\Commands\ListSet',
            'lrem'                      => '\Predis\Commands\ListRemove',
            'lpop'                      => '\Predis\Commands\ListPopFirst',
            'rpop'                      => '\Predis\Commands\ListPopLast',
            'rpoplpush'                 => '\Predis\Commands\ListPopLastPushHead',

            /* commands operating on sets */
            'sadd'                      => '\Predis\Commands\SetAdd',
            'srem'                      => '\Predis\Commands\SetRemove',
            'spop'                      => '\Predis\Commands\SetPop',
            'smove'                     => '\Predis\Commands\SetMove',
            'scard'                     => '\Predis\Commands\SetCardinality',
            'sismember'                 => '\Predis\Commands\SetIsMember',
            'sinter'                    => '\Predis\Commands\SetIntersection',
            'sinterstore'               => '\Predis\Commands\SetIntersectionStore',
            'sunion'                    => '\Predis\Commands\SetUnion',
            'sunionstore'               => '\Predis\Commands\SetUnionStore',
            'sdiff'                     => '\Predis\Commands\SetDifference',
            'sdiffstore'                => '\Predis\Commands\SetDifferenceStore',
            'smembers'                  => '\Predis\Commands\SetMembers',
            'srandmember'               => '\Predis\Commands\SetRandomMember',

            /* commands operating on sorted sets */
            'zadd'                      => '\Predis\Commands\ZSetAdd',
            'zincrby'                   => '\Predis\Commands\ZSetIncrementBy',
            'zrem'                      => '\Predis\Commands\ZSetRemove',
            'zrange'                    => '\Predis\Commands\ZSetRange',
            'zrevrange'                 => '\Predis\Commands\ZSetReverseRange',
            'zrangebyscore'             => '\Predis\Commands\ZSetRangeByScore',
            'zcard'                     => '\Predis\Commands\ZSetCardinality',
            'zscore'                    => '\Predis\Commands\ZSetScore',
            'zremrangebyscore'          => '\Predis\Commands\ZSetRemoveRangeByScore',

            /* connection related commands */
            'ping'                      => '\Predis\Commands\ConnectionPing',
            'auth'                      => '\Predis\Commands\ConnectionAuth',
            'select'                    => '\Predis\Commands\ConnectionSelect',
            'echo'                      => '\Predis\Commands\ConnectionEcho',
            'quit'                      => '\Predis\Commands\ConnectionQuit',

            /* remote server control commands */
            'info'                      => '\Predis\Commands\ServerInfo',
            'slaveof'                   => '\Predis\Commands\ServerSlaveOf',
            'monitor'                   => '\Predis\Commands\ServerMonitor',
            'dbsize'                    => '\Predis\Commands\ServerDatabaseSize',
            'flushdb'                   => '\Predis\Commands\ServerFlushDatabase',
            'flushall'                  => '\Predis\Commands\ServerFlushAll',
            'save'                      => '\Predis\Commands\ServerSave',
            'bgsave'                    => '\Predis\Commands\ServerBackgroundSave',
            'lastsave'                  => '\Predis\Commands\ServerLastSave',
            'shutdown'                  => '\Predis\Commands\ServerShutdown',
            'bgrewriteaof'              => '\Predis\Commands\ServerBackgroundRewriteAOF',
        );
    }
}

/**
 * Server profile for Redis v2.2.x.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ServerVersion22 extends ServerProfile
{
    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.2';
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedCommands()
    {
        return array(
            /* ---------------- Redis 1.2 ---------------- */

            /* commands operating on the key space */
            'exists'                    => '\Predis\Commands\KeyExists',
            'del'                       => '\Predis\Commands\KeyDelete',
            'type'                      => '\Predis\Commands\KeyType',
            'keys'                      => '\Predis\Commands\KeyKeys',
            'randomkey'                 => '\Predis\Commands\KeyRandom',
            'rename'                    => '\Predis\Commands\KeyRename',
            'renamenx'                  => '\Predis\Commands\KeyRenamePreserve',
            'expire'                    => '\Predis\Commands\KeyExpire',
            'expireat'                  => '\Predis\Commands\KeyExpireAt',
            'ttl'                       => '\Predis\Commands\KeyTimeToLive',
            'move'                      => '\Predis\Commands\KeyMove',
            'sort'                      => '\Predis\Commands\KeySort',

            /* commands operating on string values */
            'set'                       => '\Predis\Commands\StringSet',
            'setnx'                     => '\Predis\Commands\StringSetPreserve',
            'mset'                      => '\Predis\Commands\StringSetMultiple',
            'msetnx'                    => '\Predis\Commands\StringSetMultiplePreserve',
            'get'                       => '\Predis\Commands\StringGet',
            'mget'                      => '\Predis\Commands\StringGetMultiple',
            'getset'                    => '\Predis\Commands\StringGetSet',
            'incr'                      => '\Predis\Commands\StringIncrement',
            'incrby'                    => '\Predis\Commands\StringIncrementBy',
            'decr'                      => '\Predis\Commands\StringDecrement',
            'decrby'                    => '\Predis\Commands\StringDecrementBy',

            /* commands operating on lists */
            'rpush'                     => '\Predis\Commands\ListPushTail',
            'lpush'                     => '\Predis\Commands\ListPushHead',
            'llen'                      => '\Predis\Commands\ListLength',
            'lrange'                    => '\Predis\Commands\ListRange',
            'ltrim'                     => '\Predis\Commands\ListTrim',
            'lindex'                    => '\Predis\Commands\ListIndex',
            'lset'                      => '\Predis\Commands\ListSet',
            'lrem'                      => '\Predis\Commands\ListRemove',
            'lpop'                      => '\Predis\Commands\ListPopFirst',
            'rpop'                      => '\Predis\Commands\ListPopLast',
            'rpoplpush'                 => '\Predis\Commands\ListPopLastPushHead',

            /* commands operating on sets */
            'sadd'                      => '\Predis\Commands\SetAdd',
            'srem'                      => '\Predis\Commands\SetRemove',
            'spop'                      => '\Predis\Commands\SetPop',
            'smove'                     => '\Predis\Commands\SetMove',
            'scard'                     => '\Predis\Commands\SetCardinality',
            'sismember'                 => '\Predis\Commands\SetIsMember',
            'sinter'                    => '\Predis\Commands\SetIntersection',
            'sinterstore'               => '\Predis\Commands\SetIntersectionStore',
            'sunion'                    => '\Predis\Commands\SetUnion',
            'sunionstore'               => '\Predis\Commands\SetUnionStore',
            'sdiff'                     => '\Predis\Commands\SetDifference',
            'sdiffstore'                => '\Predis\Commands\SetDifferenceStore',
            'smembers'                  => '\Predis\Commands\SetMembers',
            'srandmember'               => '\Predis\Commands\SetRandomMember',

            /* commands operating on sorted sets */
            'zadd'                      => '\Predis\Commands\ZSetAdd',
            'zincrby'                   => '\Predis\Commands\ZSetIncrementBy',
            'zrem'                      => '\Predis\Commands\ZSetRemove',
            'zrange'                    => '\Predis\Commands\ZSetRange',
            'zrevrange'                 => '\Predis\Commands\ZSetReverseRange',
            'zrangebyscore'             => '\Predis\Commands\ZSetRangeByScore',
            'zcard'                     => '\Predis\Commands\ZSetCardinality',
            'zscore'                    => '\Predis\Commands\ZSetScore',
            'zremrangebyscore'          => '\Predis\Commands\ZSetRemoveRangeByScore',

            /* connection related commands */
            'ping'                      => '\Predis\Commands\ConnectionPing',
            'auth'                      => '\Predis\Commands\ConnectionAuth',
            'select'                    => '\Predis\Commands\ConnectionSelect',
            'echo'                      => '\Predis\Commands\ConnectionEcho',
            'quit'                      => '\Predis\Commands\ConnectionQuit',

            /* remote server control commands */
            'info'                      => '\Predis\Commands\ServerInfo',
            'slaveof'                   => '\Predis\Commands\ServerSlaveOf',
            'monitor'                   => '\Predis\Commands\ServerMonitor',
            'dbsize'                    => '\Predis\Commands\ServerDatabaseSize',
            'flushdb'                   => '\Predis\Commands\ServerFlushDatabase',
            'flushall'                  => '\Predis\Commands\ServerFlushAll',
            'save'                      => '\Predis\Commands\ServerSave',
            'bgsave'                    => '\Predis\Commands\ServerBackgroundSave',
            'lastsave'                  => '\Predis\Commands\ServerLastSave',
            'shutdown'                  => '\Predis\Commands\ServerShutdown',
            'bgrewriteaof'              => '\Predis\Commands\ServerBackgroundRewriteAOF',


            /* ---------------- Redis 2.0 ---------------- */

            /* commands operating on string values */
            'setex'                     => '\Predis\Commands\StringSetExpire',
            'append'                    => '\Predis\Commands\StringAppend',
            'substr'                    => '\Predis\Commands\StringSubstr',

            /* commands operating on lists */
            'blpop'                     => '\Predis\Commands\ListPopFirstBlocking',
            'brpop'                     => '\Predis\Commands\ListPopLastBlocking',

            /* commands operating on sorted sets */
            'zunionstore'               => '\Predis\Commands\ZSetUnionStore',
            'zinterstore'               => '\Predis\Commands\ZSetIntersectionStore',
            'zcount'                    => '\Predis\Commands\ZSetCount',
            'zrank'                     => '\Predis\Commands\ZSetRank',
            'zrevrank'                  => '\Predis\Commands\ZSetReverseRank',
            'zremrangebyrank'           => '\Predis\Commands\ZSetRemoveRangeByRank',

            /* commands operating on hashes */
            'hset'                      => '\Predis\Commands\HashSet',
            'hsetnx'                    => '\Predis\Commands\HashSetPreserve',
            'hmset'                     => '\Predis\Commands\HashSetMultiple',
            'hincrby'                   => '\Predis\Commands\HashIncrementBy',
            'hget'                      => '\Predis\Commands\HashGet',
            'hmget'                     => '\Predis\Commands\HashGetMultiple',
            'hdel'                      => '\Predis\Commands\HashDelete',
            'hexists'                   => '\Predis\Commands\HashExists',
            'hlen'                      => '\Predis\Commands\HashLength',
            'hkeys'                     => '\Predis\Commands\HashKeys',
            'hvals'                     => '\Predis\Commands\HashValues',
            'hgetall'                   => '\Predis\Commands\HashGetAll',

            /* transactions */
            'multi'                     => '\Predis\Commands\TransactionMulti',
            'exec'                      => '\Predis\Commands\TransactionExec',
            'discard'                   => '\Predis\Commands\TransactionDiscard',

            /* publish - subscribe */
            'subscribe'                 => '\Predis\Commands\PubSubSubscribe',
            'unsubscribe'               => '\Predis\Commands\PubSubUnsubscribe',
            'psubscribe'                => '\Predis\Commands\PubSubSubscribeByPattern',
            'punsubscribe'              => '\Predis\Commands\PubSubUnsubscribeByPattern',
            'publish'                   => '\Predis\Commands\PubSubPublish',

            /* remote server control commands */
            'config'                    => '\Predis\Commands\ServerConfig',


            /* ---------------- Redis 2.2 ---------------- */

            /* commands operating on the key space */
            'persist'                   => '\Predis\Commands\KeyPersist',

            /* commands operating on string values */
            'strlen'                    => '\Predis\Commands\StringStrlen',
            'setrange'                  => '\Predis\Commands\StringSetRange',
            'getrange'                  => '\Predis\Commands\StringGetRange',
            'setbit'                    => '\Predis\Commands\StringSetBit',
            'getbit'                    => '\Predis\Commands\StringGetBit',

            /* commands operating on lists */
            'rpushx'                    => '\Predis\Commands\ListPushTailX',
            'lpushx'                    => '\Predis\Commands\ListPushHeadX',
            'linsert'                   => '\Predis\Commands\ListInsert',
            'brpoplpush'                => '\Predis\Commands\ListPopLastPushHeadBlocking',

            /* commands operating on sorted sets */
            'zrevrangebyscore'          => '\Predis\Commands\ZSetReverseRangeByScore',

            /* transactions */
            'watch'                     => '\Predis\Commands\TransactionWatch',
            'unwatch'                   => '\Predis\Commands\TransactionUnwatch',

            /* remote server control commands */
            'object'                    => '\Predis\Commands\ServerObject',
        );
    }
}

/* --------------------------------------------------------------------------- */

namespace Predis\Options;

use Predis\Network\IConnectionCluster;
use Predis\Network\PredisCluster;
use Predis\IConnectionFactory;
use Predis\ConnectionFactory;
use Predis\Commands\Processors\KeyPrefixProcessor;
use Predis\Profiles\ServerProfile;
use Predis\Profiles\IServerProfile;

/**
 * Interface that defines a client option.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IOption
{
    /**
     * Validates (and optionally converts) the passed value.
     *
     * @param mixed $value Input value.
     * @return mixed
     */
    public function validate($value);

    /**
     * Returns a default value for the option.
     *
     * @param mixed $value Input value.
     * @return mixed
     */
    public function getDefault();

    /**
     * Validates a value and, if no value is specified, returns
     * the default one defined by the option.
     *
     * @param mixed $value Input value.
     * @return mixed
     */
    public function __invoke($value);
}

/**
 * Implements a client option.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class Option implements IOption
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($value)
    {
        if (isset($value)) {
            return $this->validate($value);
        }

        return $this->getDefault();
    }
}

/**
 * Implements a generic class used to dinamically define a client option.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class CustomOption implements IOption
{
    private $_validate;
    private $_default;

    /**
     * @param array $options List of options
     */
    public function __construct(Array $options)
    {
        $this->_validate = $this->filterCallable($options, 'validate');
        $this->_default  = $this->filterCallable($options, 'default');
    }

    /**
     * Checks if the specified value in the options array is a callable object.
     *
     * @param array $options Array of options
     * @param string $key Target option.
     */
    private function filterCallable($options, $key)
    {
        if (!isset($options[$key])) {
            return;
        }

        $callable = $options[$key];
        if (is_callable($callable)) {
            return $callable;
        }

        throw new \InvalidArgumentException("The parameter $key must be callable");
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if (isset($value)) {
            if ($this->_validate === null) {
                return $value;
            }
            $validator = $this->_validate;

            return $validator($value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault()
    {
        if (!isset($this->_default)) {
            return;
        }
        $default = $this->_default;

        return $default();
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($value)
    {
        if (isset($value)) {
            return $this->validate($value);
        }

        return $this->getDefault();
    }
}

/**
 * Option class that handles server profiles to be used by a client.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ClientProfile extends Option
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if ($value instanceof IServerProfile) {
            return $value;
        }

        if (is_string($value)) {
            return ServerProfile::get($value);
        }

        throw new \InvalidArgumentException(
            "Invalid value for the profile option"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault()
    {
        return ServerProfile::getDefault();
    }
}

/**
 * Option class that returns a connection factory to be used by a client.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ClientConnectionFactory extends Option
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if ($value instanceof IConnectionFactory) {
            return $value;
        }
        if (is_array($value)) {
            return new ConnectionFactory($value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault()
    {
        return new ConnectionFactory();
    }
}

/**
 * Option class that handles the prefixing of keys in commands.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ClientPrefix extends Option
{
    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        return new KeyPrefixProcessor($value);
    }
}

/**
 * Option class that returns a connection cluster to be used by a client.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ClientCluster extends Option
{
    /**
     * Checks if the specified value is a valid instance of IConnectionCluster.
     *
     * @param IConnectionCluster $cluster Instance of a connection cluster.
     * @return IConnectionCluster
     */
    protected function checkInstance($cluster)
    {
        if (!$cluster instanceof IConnectionCluster) {
            throw new \InvalidArgumentException(
                'Instance of Predis\Network\IConnectionCluster expected'
            );
        }

        return $cluster;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if (is_callable($value)) {
            return $this->checkInstance(call_user_func($value));
        }
        $initializer = $this->getInitializer($value);

        return $this->checkInstance($initializer());
    }

    /**
     * Returns an initializer for the specified FQN or type.
     *
     * @param string $fqnOrType Type of cluster of FQN of a class implementing IConnectionCluster
     * @return \Closure
     */
    protected function getInitializer($fqnOrType)
    {
        switch ($fqnOrType) {
            case 'predis':
                return function() { return new PredisCluster(); };

            default:
                return function() use($fqnOrType) {
                    return new $fqnOrType();
                };
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault()
    {
        return new PredisCluster();
    }
}

/* --------------------------------------------------------------------------- */

namespace Predis\Commands\Processors;

use Predis\Commands\ICommand;

/**
 * Defines an object that can process commands using command processors.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IProcessingSupport
{
    /**
     * Associates a command processor.
     *
     * @param ICommandProcessor $processor The command processor.
     */
    public function setProcessor(ICommandProcessor $processor);

    /**
     * Returns the associated command processor.
     *
     * @return ICommandProcessor
     */
    public function getProcessor();
}

/**
 * A command processor processes commands before they are sent to Redis.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface ICommandProcessor
{
    /**
     * Processes a Redis command.
     *
     * @param ICommand $command Redis command.
     */
    public function process(ICommand $command);
}

/**
 * A command processor chain processes a command using multiple chained command
 * processor before it is sent to Redis.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface ICommandProcessorChain extends ICommandProcessor, \IteratorAggregate, \Countable
{
    /**
     * Adds a command processor.
     *
     * @param ICommandProcessor $processor A command processor.
     */
    public function add(ICommandProcessor $processor);

    /**
     * Removes a command processor from the chain.
     *
     * @param ICommandProcessor $processor A command processor.
     */
    public function remove(ICommandProcessor $processor);

    /**
     * Returns an ordered list of the command processors in the chain.
     *
     * @return array
     */
    public function getProcessors();
}

/**
 * Default implementation of a command processors chain.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ProcessorChain implements ICommandProcessorChain, \ArrayAccess
{
    private $_processors;

    /**
     * @param array $processors List of instances of ICommandProcessor.
     */
    public function __construct($processors = array())
    {
        foreach ($processors as $processor) {
            $this->add($processor);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(ICommandProcessor $processor)
    {
        $this->_processors[] = $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ICommandProcessor $processor)
    {
        $index = array_search($processor, $this->_processors, true);
        if ($index !== false) {
            unset($this[$index]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(ICommand $command)
    {
        $count = count($this->_processors);
        for ($i = 0; $i < $count; $i++) {
            $this->_processors[$i]->process($command);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessors()
    {
        return $this->_processors;
    }

    /**
     * Returns an iterator over the list of command processor in the chain.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_processors);
    }

    /**
     * Returns the number of command processors in the chain.
     *
     * @return int
     */
    public function count()
    {
        return count($this->_processors);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($index)
    {
        return isset($this->_processors[$index]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($index)
    {
        return $this->_processors[$index];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($index, $processor)
    {
        if (!$processor instanceof ICommandProcessor) {
            throw new \InvalidArgumentException(
                'A processor chain can hold only instances of classes implementing '.
                'the Predis\Commands\Preprocessors\ICommandProcessor interface'
            );
        }

        $this->_processors[$index] = $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($index)
    {
        unset($this->_processors[$index]);
    }
}

/**
 * Command processor that is used to prefix the keys contained in the arguments
 * of a Redis command.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class KeyPrefixProcessor implements ICommandProcessor
{
    private $_prefix;

    /**
     * @param string $prefix Prefix for the keys.
     */
    public function __construct($prefix)
    {
        $this->setPrefix($prefix);
    }

    /**
     * Sets a prefix that is applied to all the keys.
     *
     * @param string $prefix Prefix for the keys.
     */
    public function setPrefix($prefix)
    {
        $this->_prefix = $prefix;
    }

    /**
     * Gets the current prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ICommand $command)
    {
        $command->prefixKeys($this->_prefix);
    }
}

/* --------------------------------------------------------------------------- */

namespace Predis\Protocol\Text;

use Predis\Commands\ICommand;
use Predis\Protocol\IResponseReader;
use Predis\Protocol\ICommandSerializer;
use Predis\Protocol\IComposableProtocolProcessor;
use Predis\Network\IConnectionComposable;
use Predis\Helpers;
use Predis\Protocol\IResponseHandler;
use Predis\Protocol\ProtocolException;
use Predis\ServerException;
use Predis\ResponseError;
use Predis\Iterators\MultiBulkResponseSimple;
use Predis\ResponseQueued;
use Predis\Protocol\IProtocolProcessor;

/**
 * Implements a response handler for status replies using the standard wire
 * protocol defined by Redis.
 *
 * @link http://redis.io/topics/protocol
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ResponseStatusHandler implements IResponseHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(IConnectionComposable $connection, $status)
    {
        switch ($status) {
            case 'OK':
                return true;

            case 'QUEUED':
                return new ResponseQueued();

            default:
                return $status;
        }
    }
}

/**
 * Implements a pluggable command serializer using the standard  wire protocol
 * defined by Redis.
 *
 * @link http://redis.io/topics/protocol
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class TextCommandSerializer implements ICommandSerializer
{
    /**
     * {@inheritdoc}
     */
    public function serialize(ICommand $command)
    {
        $commandId = $command->getId();
        $arguments = $command->getArguments();

        $cmdlen = strlen($commandId);
        $reqlen = count($arguments) + 1;

        $buffer = "*{$reqlen}\r\n\${$cmdlen}\r\n{$commandId}\r\n";

        for ($i = 0; $i < $reqlen - 1; $i++) {
            $argument = $arguments[$i];
            $arglen = strlen($argument);
            $buffer .= "\${$arglen}\r\n{$argument}\r\n";
        }

        return $buffer;
    }
}

/**
 * Implements a protocol processor for the standard wire protocol defined by Redis.
 *
 * @link http://redis.io/topics/protocol
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class TextProtocol implements IProtocolProcessor
{
    const NEWLINE = "\r\n";
    const OK      = 'OK';
    const ERROR   = 'ERR';
    const QUEUED  = 'QUEUED';
    const NULL    = 'nil';

    const PREFIX_STATUS     = '+';
    const PREFIX_ERROR      = '-';
    const PREFIX_INTEGER    = ':';
    const PREFIX_BULK       = '$';
    const PREFIX_MULTI_BULK = '*';

    const BUFFER_SIZE = 4096;

    private $_mbiterable;
    private $_throwErrors;
    private $_serializer;

    /**
     *
     */
    public function __construct()
    {
        $this->_mbiterable  = false;
        $this->_throwErrors = true;
        $this->_serializer  = new TextCommandSerializer();
    }

    /**
     * {@inheritdoc}
     */
    public function write(IConnectionComposable $connection, ICommand $command)
    {
        $connection->writeBytes($this->_serializer->serialize($command));
    }

    /**
     * {@inheritdoc}
     */
    public function read(IConnectionComposable $connection)
    {
        $chunk = $connection->readLine();
        $prefix = $chunk[0];
        $payload = substr($chunk, 1);

        switch ($prefix) {
            case '+':    // inline
                switch ($payload) {
                    case 'OK':
                        return true;

                    case 'QUEUED':
                        return new ResponseQueued();

                    default:
                        return $payload;
                }

            case '$':    // bulk
                $size = (int) $payload;
                if ($size === -1) {
                    return null;
                }
                return substr($connection->readBytes($size + 2), 0, -2);

            case '*':    // multi bulk
                $count = (int) $payload;

                if ($count === -1) {
                    return null;
                }
                if ($this->_mbiterable == true) {
                    return new MultiBulkResponseSimple($connection, $count);
                }

                $multibulk = array();
                for ($i = 0; $i < $count; $i++) {
                    $multibulk[$i] = $this->read($connection);
                }

                return $multibulk;

            case ':':    // integer
                return (int) $payload;

            case '-':    // error
                if ($this->_throwErrors) {
                    throw new ServerException($payload);
                }
                return new ResponseError($payload);

            default:
                Helpers::onCommunicationException(new ProtocolException(
                    $connection, "Unknown prefix: '$prefix'"
                ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($option, $value)
    {
        switch ($option) {
            case 'iterable_multibulk':
                $this->_mbiterable = (bool) $value;
                break;

            case 'throw_errors':
                $this->_throwErrors = (bool) $value;
                break;
        }
    }
}

/**
 * Implements a pluggable response reader using the standard wire protocol
 * defined by Redis.
 *
 * @link http://redis.io/topics/protocol
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class TextResponseReader implements IResponseReader
{
    private $_prefixHandlers;

    /**
     *
     */
    public function __construct()
    {
        $this->_prefixHandlers = $this->getDefaultHandlers();
    }

    /**
     * Returns the default set of response handlers for all the type of replies
     * that can be returned by Redis.
     */
    private function getDefaultHandlers()
    {
        return array(
            TextProtocol::PREFIX_STATUS     => new ResponseStatusHandler(),
            TextProtocol::PREFIX_ERROR      => new ResponseErrorHandler(),
            TextProtocol::PREFIX_INTEGER    => new ResponseIntegerHandler(),
            TextProtocol::PREFIX_BULK       => new ResponseBulkHandler(),
            TextProtocol::PREFIX_MULTI_BULK => new ResponseMultiBulkHandler(),
        );
    }

    /**
     * Sets a response handler for a certain prefix that identifies a type of
     * reply that can be returned by Redis.
     *
     * @param string $prefix Identifier for a type of reply.
     * @param IResponseHandler $handler Response handler for the reply.
     */
    public function setHandler($prefix, IResponseHandler $handler)
    {
        $this->_prefixHandlers[$prefix] = $handler;
    }

    /**
     * Returns the response handler associated to a certain type of reply that
     * can be returned by Redis.
     *
     * @param string $prefix Identifier for a type of reply.
     * @return IResponseHandler
     */
    public function getHandler($prefix)
    {
        if (isset($this->_prefixHandlers[$prefix])) {
            return $this->_prefixHandlers[$prefix];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read(IConnectionComposable $connection)
    {
        $header = $connection->readLine();
        if ($header === '') {
            $this->protocolError($connection, 'Unexpected empty header');
        }

        $prefix = $header[0];
        if (!isset($this->_prefixHandlers[$prefix])) {
            $this->protocolError($connection, "Unknown prefix '$prefix'");
        }

        $handler = $this->_prefixHandlers[$prefix];

        return $handler->handle($connection, substr($header, 1));
    }

    /**
     * Helper method used to handle a protocol error generated while reading a
     * reply from a connection to Redis.
     *
     * @param IConnectionComposable $connection Connection to Redis that generated the error.
     * @param string $message Error message.
     */
    private function protocolError(IConnectionComposable $connection, $message)
    {
        Helpers::onCommunicationException(new ProtocolException($connection, $message));
    }
}

/**
 * Implements a response handler for iterable multi-bulk replies using the
 * standard wire protocol defined by Redis.
 *
 * @link http://redis.io/topics/protocol
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ResponseMultiBulkStreamHandler implements IResponseHandler
{
    /**
     * Handles a multi-bulk reply returned by Redis in a streamable fashion.
     *
     * @param IConnectionComposable $connection Connection to Redis.
     * @param string $lengthString Number of items in the multi-bulk reply.
     * @return MultiBulkResponseSimple
     */
    public function handle(IConnectionComposable $connection, $lengthString)
    {
        $length = (int) $lengthString;

        if ($length != $lengthString) {
            Helpers::onCommunicationException(new ProtocolException(
                $connection, "Cannot parse '$length' as data length"
            ));
        }

        return new MultiBulkResponseSimple($connection, $length);
    }
}

/**
 * Implements a response handler for multi-bulk replies using the standard
 * wire protocol defined by Redis.
 *
 * @link http://redis.io/topics/protocol
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ResponseMultiBulkHandler implements IResponseHandler
{
    /**
     * Handles a multi-bulk reply returned by Redis.
     *
     * @param IConnectionComposable $connection Connection to Redis.
     * @param string $lengthString Number of items in the multi-bulk reply.
     * @return array
     */
    public function handle(IConnectionComposable $connection, $lengthString)
    {
        $length = (int) $lengthString;

        if ($length != $lengthString) {
            Helpers::onCommunicationException(new ProtocolException(
                $connection, "Cannot parse '$length' as data length"
            ));
        }

        if ($length === -1) {
            return null;
        }

        $list = array();

        if ($length > 0) {
            $handlersCache = array();
            $reader = $connection->getProtocol()->getReader();

            for ($i = 0; $i < $length; $i++) {
                $header = $connection->readLine();
                $prefix = $header[0];

                if (isset($handlersCache[$prefix])) {
                    $handler = $handlersCache[$prefix];
                }
                else {
                    $handler = $reader->getHandler($prefix);
                    $handlersCache[$prefix] = $handler;
                }

                $list[$i] = $handler->handle($connection, substr($header, 1));
            }
        }

        return $list;
    }
}

/**
 * Implements a response handler for bulk replies using the standard wire
 * protocol defined by Redis.
 *
 * @link http://redis.io/topics/protocol
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ResponseBulkHandler implements IResponseHandler
{
    /**
     * Handles a bulk reply returned by Redis.
     *
     * @param IConnectionComposable $connection Connection to Redis.
     * @param string $lengthString Bytes size of the bulk reply.
     * @return string
     */
    public function handle(IConnectionComposable $connection, $lengthString)
    {
        $length = (int) $lengthString;

        if ($length != $lengthString) {
            Helpers::onCommunicationException(new ProtocolException(
                $connection, "Cannot parse '$length' as data length"
            ));
        }

        if ($length >= 0) {
            return substr($connection->readBytes($length + 2), 0, -2);
        }

        if ($length == -1) {
            return null;
        }
    }
}

/**
 * Implements a response handler for error replies using the standard wire
 * protocol defined by Redis.
 *
 * This handler throws an exception to notify the user that an error has
 * occurred on the server.
 *
 * @link http://redis.io/topics/protocol
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ResponseErrorHandler implements IResponseHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(IConnectionComposable $connection, $errorMessage)
    {
        throw new ServerException($errorMessage);
    }
}

/**
 * Implements a response handler for error replies using the standard wire
 * protocol defined by Redis.
 *
 * This handler returns a reply object to notify the user that an error has
 * occurred on the server.
 *
 * @link http://redis.io/topics/protocol
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ResponseErrorSilentHandler implements IResponseHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(IConnectionComposable $connection, $errorMessage)
    {
        return new ResponseError($errorMessage);
    }
}

/**
 * Implements a response handler for integer replies using the standard wire
 * protocol defined by Redis.
 *
 * @link http://redis.io/topics/protocol
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ResponseIntegerHandler implements IResponseHandler
{
    /**
     * Handles an integer reply returned by Redis.
     *
     * @param IConnectionComposable $connection Connection to Redis.
     * @param string $number String representation of an integer.
     * @return int
     */
    public function handle(IConnectionComposable $connection, $number)
    {
        if (is_numeric($number)) {
            return (int) $number;
        }

        if ($number !== 'nil') {
            Helpers::onCommunicationException(new ProtocolException(
                $connection, "Cannot parse '$number' as numeric response"
            ));
        }

        return null;
    }
}

/**
 * Implements a customizable protocol processor that uses the standard Redis
 * wire protocol to serialize Redis commands and parse replies returned by
 * the server using a pluggable set of classes.
 *
 * @link http://redis.io/topics/protocol
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ComposableTextProtocol implements IComposableProtocolProcessor
{
    private $_serializer;
    private $_reader;

    /**
     * @param array $options Set of options used to initialize the protocol processor.
     */
    public function __construct(Array $options = array())
    {
        $this->setSerializer(new TextCommandSerializer());
        $this->setReader(new TextResponseReader());

        if (count($options) > 0) {
            $this->initializeOptions($options);
        }
    }

    /**
     * Initializes the protocol processor using a set of options.
     *
     * @param array $options Set of options.
     */
    private function initializeOptions(Array $options)
    {
        foreach ($options as $k => $v) {
            $this->setOption($k, $v);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($option, $value)
    {
        switch ($option) {
            case 'iterable_multibulk':
                $handler = $value ? new ResponseMultiBulkStreamHandler() : new ResponseMultiBulkHandler();
                $this->_reader->setHandler(TextProtocol::PREFIX_MULTI_BULK, $handler);
                break;

            case 'throw_errors':
                $handler = $value ? new ResponseErrorHandler() : new ResponseErrorSilentHandler();
                $this->_reader->setHandler(TextProtocol::PREFIX_ERROR, $handler);
                break;

            default:
                throw new \InvalidArgumentException("The option $option is not supported by the current protocol");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(ICommand $command)
    {
        return $this->_serializer->serialize($command);
    }

    /**
     * {@inheritdoc}
     */
    public function write(IConnectionComposable $connection, ICommand $command)
    {
        $connection->writeBytes($this->_serializer->serialize($command));
    }

    /**
     * {@inheritdoc}
     */
    public function read(IConnectionComposable $connection)
    {
        return $this->_reader->read($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(ICommandSerializer $serializer)
    {
        $this->_serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getSerializer()
    {
        return $this->_serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function setReader(IResponseReader $reader)
    {
        $this->_reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function getReader()
    {
        return $this->_reader;
    }
}

/* --------------------------------------------------------------------------- */

namespace Predis\Distribution;

/**
 * A generator of node keys implements the logic used to calculate the hash of
 * a key to distribute the respective operations among nodes.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface INodeKeyGenerator
{
    /**
     * Generates an hash that is used by the distributor algorithm
     *
     * @param string $value Value used to generate the hash.
     * @return int
     */
    public function generateKey($value);
}

/**
 * A distributor implements the logic to automatically distribute
 * keys among several nodes for client-side sharding.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IDistributionStrategy extends INodeKeyGenerator
{
    /**
     * Adds a node to the distributor with an optional weight.
     *
     * @param mixed $node Node object.
     * @param int $weight Weight for the node.
     */
    public function add($node, $weight = null);

    /**
     * Removes a node from the distributor.
     *
     * @param mixed $node Node object.
     */
    public function remove($node);

    /**
     * Gets a node from the distributor using the computed hash of a key.
     *
     * @return mixed
     */
    public function get($key);
}

/**
 * This class implements an hashring-based distributor that uses the same
 * algorithm of memcache to distribute keys in a cluster using client-side
 * sharding.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 * @author Lorenzo Castelli <lcastelli@gmail.com>
 */
class HashRing implements IDistributionStrategy
{
    const DEFAULT_REPLICAS = 128;
    const DEFAULT_WEIGHT   = 100;

    private $_nodes;
    private $_ring;
    private $_ringKeys;
    private $_ringKeysCount;
    private $_replicas;

    /**
     * @param int $replicas Number of replicas in the ring.
     */
    public function __construct($replicas = self::DEFAULT_REPLICAS)
    {
        $this->_replicas = $replicas;
        $this->_nodes    = array();
    }

    /**
     * Adds a node to the ring with an optional weight.
     *
     * @param mixed $node Node object.
     * @param int $weight Weight for the node.
     */
    public function add($node, $weight = null)
    {
        // In case of collisions in the hashes of the nodes, the node added
        // last wins, thus the order in which nodes are added is significant.
        $this->_nodes[] = array('object' => $node, 'weight' => (int) $weight ?: $this::DEFAULT_WEIGHT);
        $this->reset();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($node)
    {
        // A node is removed by resetting the ring so that it's recreated from
        // scratch, in order to reassign possible hashes with collisions to the
        // right node according to the order in which they were added in the
        // first place.
        for ($i = 0; $i < count($this->_nodes); ++$i) {
            if ($this->_nodes[$i]['object'] === $node) {
                array_splice($this->_nodes, $i, 1);
                $this->reset();
                break;
            }
        }
    }

    /**
     * Resets the distributor.
     */
    private function reset()
    {
        unset(
            $this->_ring,
            $this->_ringKeys,
            $this->_ringKeysCount
        );
    }

    /**
     * Returns the initialization status of the distributor.
     *
     * @return Boolean
     */
    private function isInitialized()
    {
        return isset($this->_ringKeys);
    }

    /**
     * Calculates the total weight of all the nodes in the distributor.
     *
     * @return int
     */
    private function computeTotalWeight()
    {
        $totalWeight = 0;
        foreach ($this->_nodes as $node) {
            $totalWeight += $node['weight'];
        }

        return $totalWeight;
    }

    /**
     * Initializes the distributor.
     */
    private function initialize()
    {
        if ($this->isInitialized()) {
            return;
        }

        if (count($this->_nodes) === 0) {
            throw new EmptyRingException('Cannot initialize empty hashring');
        }

        $this->_ring = array();
        $totalWeight = $this->computeTotalWeight();
        $nodesCount  = count($this->_nodes);

        foreach ($this->_nodes as $node) {
            $weightRatio = $node['weight'] / $totalWeight;
            $this->addNodeToRing($this->_ring, $node, $nodesCount, $this->_replicas, $weightRatio);
        }
        ksort($this->_ring, SORT_NUMERIC);

        $this->_ringKeys = array_keys($this->_ring);
        $this->_ringKeysCount = count($this->_ringKeys);
    }

    /**
     * Implements the logic needed to add a node to the hashring.
     *
     * @param array $ring Source hashring.
     * @param mixed $node Node object to be added.
     * @param int $totalNodes Total number of nodes.
     * @param int $replicas Number of replicas in the ring.
     * @param float $weightRatio Weight ratio for the node.
     */
    protected function addNodeToRing(&$ring, $node, $totalNodes, $replicas, $weightRatio)
    {
        $nodeObject = $node['object'];
        $nodeHash = $this->getNodeHash($nodeObject);
        $replicas = (int) round($weightRatio * $totalNodes * $replicas);

        for ($i = 0; $i < $replicas; $i++) {
            $key = crc32("$nodeHash:$i");
            $ring[$key] = $nodeObject;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getNodeHash($nodeObject)
    {
        return (string) $nodeObject;
    }

    /**
     * Calculates the hash for the specified value.
     *
     * @param string $value Input value.
     * @return int
     */
    public function generateKey($value)
    {
        return crc32($value);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->_ring[$this->getNodeKey($key)];
    }

    /**
     * Calculates the corrisponding key of a node distributed in the hashring.
     *
     * @param int $key Computed hash of a key.
     * @return int
     */
    private function getNodeKey($key)
    {
        $this->initialize();
        $ringKeys = $this->_ringKeys;
        $upper = $this->_ringKeysCount - 1;
        $lower = 0;

        while ($lower <= $upper) {
            $index = ($lower + $upper) >> 1;
            $item  = $ringKeys[$index];
            if ($item > $key) {
                $upper = $index - 1;
            }
            else if ($item < $key) {
                $lower = $index + 1;
            }
            else {
                return $item;
            }
        }

        return $ringKeys[$this->wrapAroundStrategy($upper, $lower, $this->_ringKeysCount)];
    }

    /**
     * Implements a strategy to deal with wrap-around errors during binary searches.
     *
     * @param int $upper
     * @param int $lower
     * @param int $ringKeysCount
     * @return int
     */
    protected function wrapAroundStrategy($upper, $lower, $ringKeysCount)
    {
        // Binary search for the last item in ringkeys with a value less or
        // equal to the key. If no such item exists, return the last item.
        return $upper >= 0 ? $upper : $ringKeysCount - 1;
    }
}

/**
 * This class implements an hashring-based distributor that uses the same
 * algorithm of libketama to distribute keys in a cluster using client-side
 * sharding.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 * @author Lorenzo Castelli <lcastelli@gmail.com>
 */
class KetamaPureRing extends HashRing
{
    const DEFAULT_REPLICAS = 160;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct($this::DEFAULT_REPLICAS);
    }

    /**
     * {@inheritdoc}
     */
    protected function addNodeToRing(&$ring, $node, $totalNodes, $replicas, $weightRatio)
    {
        $nodeObject = $node['object'];
        $nodeHash = $this->getNodeHash($nodeObject);
        $replicas = (int) floor($weightRatio * $totalNodes * ($replicas / 4));

        for ($i = 0; $i < $replicas; $i++) {
            $unpackedDigest = unpack('V4', md5("$nodeHash-$i", true));
            foreach ($unpackedDigest as $key) {
                $ring[$key] = $nodeObject;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateKey($value)
    {
        $hash = unpack('V', md5($value, true));
        return $hash[1];
    }

    /**
     * {@inheritdoc}
     */
    protected function wrapAroundStrategy($upper, $lower, $ringKeysCount)
    {
        // Binary search for the first item in _ringkeys with a value greater
        // or equal to the key. If no such item exists, return the first item.
        return $lower < $ringKeysCount ? $lower : 0;
    }
}

/**
 * Exception class that identifies empty rings.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class EmptyRingException extends \Exception
{
}

/* --------------------------------------------------------------------------- */

namespace Predis\Pipeline;

use Predis\Network\IConnection;
use Predis\Client;
use Predis\Helpers;
use Predis\ClientException;
use Predis\Commands\ICommand;
use Predis\ServerException;
use Predis\CommunicationException;

/**
 * Defines a strategy to write a list of commands to the network
 * and read back their replies.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
interface IPipelineExecutor
{
    /**
     * Writes a list of commands to the network and reads back their replies.
     *
     * @param IConnection $connection Connection to Redis.
     * @param array $commands List of commands.
     * @return array
     */
    public function execute(IConnection $connection, &$commands);
}

/**
 * Implements the standard pipeline executor strategy used
 * to write a list of commands and read their replies over
 * a connection to Redis.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class StandardExecutor implements IPipelineExecutor
{
    /**
     * {@inheritdoc}
     */
    public function execute(IConnection $connection, &$commands)
    {
        $sizeofPipe = count($commands);
        $values = array();

        foreach ($commands as $command) {
            $connection->writeCommand($command);
        }

        try {
            for ($i = 0; $i < $sizeofPipe; $i++) {
                $response = $connection->readResponse($commands[$i]);
                $values[] = $response instanceof \Iterator
                    ? iterator_to_array($response)
                    : $response;
                unset($commands[$i]);
            }
        }
        catch (ServerException $exception) {
            // Force disconnection to prevent protocol desynchronization.
            $connection->disconnect();
            throw $exception;
        }

        return $values;
    }
}

/**
 * Implements a pipeline executor strategy that does not fail when an error is
 * encountered, but adds the returned error in the replies array.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SafeExecutor implements IPipelineExecutor
{
    /**
     * {@inheritdoc}
     */
    public function execute(IConnection $connection, &$commands)
    {
        $sizeofPipe = count($commands);
        $values = array();

        foreach ($commands as $command) {
            try {
                $connection->writeCommand($command);
            }
            catch (CommunicationException $exception) {
                return array_fill(0, $sizeofPipe, $exception);
            }
        }

        for ($i = 0; $i < $sizeofPipe; $i++) {
            $command = $commands[$i];
            unset($commands[$i]);

            try {
                $response = $connection->readResponse($command);
                $values[] = $response instanceof \Iterator ? iterator_to_array($response) : $response;
            }
            catch (ServerException $exception) {
                $values[] = $exception->toResponseError();
            }
            catch (CommunicationException $exception) {
                $toAdd = count($commands) - count($values);
                $values = array_merge($values, array_fill(0, $toAdd, $exception));
                break;
            }
        }

        return $values;
    }
}

/**
 * Abstraction of a pipeline context where write and read operations
 * of commands and their replies over the network are pipelined.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class PipelineContext
{
    private $_client;
    private $_executor;

    private $_pipeline = array();
    private $_replies  = array();
    private $_running  = false;

    /**
     * @param Client Client instance used by the context.
     * @param array Options for the context initialization.
     */
    public function __construct(Client $client, Array $options = null)
    {
        $this->_client = $client;
        $this->_executor = $this->getExecutor($client, $options ?: array());
    }

    /**
     * Returns a pipeline executor depending on the kind of the underlying
     * connection and the passed options.
     *
     * @param Client Client instance used by the context.
     * @param array Options for the context initialization.
     * @return IPipelineExecutor
     */
    protected function getExecutor(Client $client, Array $options)
    {
        if (!$options) {
            return new StandardExecutor();
        }

        if (isset($options['executor'])) {
            $executor = $options['executor'];
            if (!$executor instanceof IPipelineExecutor) {
                throw new \InvalidArgumentException(
                    'The executor option accepts only instances ' .
                    'of Predis\Pipeline\IPipelineExecutor'
                );
            }
            return $executor;
        }

        if (isset($options['safe']) && $options['safe'] == true) {
            $isCluster = Helpers::isCluster($client->getConnection());
            return $isCluster ? new SafeClusterExecutor() : new SafeExecutor();
        }

        return new StandardExecutor();
    }

    /**
     * Queues a command into the pipeline buffer.
     *
     * @param string $method Command ID.
     * @param array $arguments Arguments for the command.
     * @return PipelineContext
     */
    public function __call($method, $arguments)
    {
        $command = $this->_client->createCommand($method, $arguments);
        $this->recordCommand($command);

        return $this;
    }

    /**
     * Queues a command instance into the pipeline buffer.
     */
    protected function recordCommand(ICommand $command)
    {
        $this->_pipeline[] = $command;
    }

    /**
     * Queues a command instance into the pipeline buffer.
     */
    public function executeCommand(ICommand $command)
    {
        $this->recordCommand($command);
    }

    /**
     * Flushes the queued commands by writing the buffer to Redis and reading
     * all the replies into the reply buffer.
     *
     * @return PipelineContext
     */
    public function flushPipeline()
    {
        if (count($this->_pipeline) > 0) {
            $connection = $this->_client->getConnection();
            $replies = $this->_executor->execute($connection, $this->_pipeline);
            $this->_replies = array_merge($this->_replies, $replies);
            $this->_pipeline = array();
        }

        return $this;
    }

    /**
     * Marks the running status of the pipeline.
     *
     * @param Boolean $bool True if the pipeline is running.
     *                      False if the pipeline is not running.
     */
    private function setRunning($bool)
    {
        if ($bool === true && $this->_running === true) {
            throw new ClientException("This pipeline is already opened");
        }
        $this->_running = $bool;
    }

    /**
     * Handles the actual execution of the whole pipeline.
     *
     * @param mixed $callable Callback for execution.
     * @return array
     */
    public function execute($callable = null)
    {
        if ($callable && !is_callable($callable)) {
            throw new \InvalidArgumentException('Argument passed must be a callable object');
        }

        $this->setRunning(true);
        $pipelineBlockException = null;

        try {
            if ($callable !== null) {
                $callable($this);
            }
            $this->flushPipeline();
        }
        catch (\Exception $exception) {
            $pipelineBlockException = $exception;
        }

        $this->setRunning(false);

        if ($pipelineBlockException !== null) {
            throw $pipelineBlockException;
        }

        return $this->_replies;
    }
}

/**
 * Implements a pipeline executor strategy that writes a list of commands to
 * the connection object but does not read back their replies.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class FireAndForgetExecutor implements IPipelineExecutor
{
    /**
     * {@inheritdoc}
     */
    public function execute(IConnection $connection, &$commands)
    {
        foreach ($commands as $command) {
            $connection->writeCommand($command);
        }

        $connection->disconnect();

        return array();
    }
}

/**
 * Implements a pipeline executor strategy for connection clusters that does
 * not fail when an error is encountered, but adds the returned error in the
 * replies array.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class SafeClusterExecutor implements IPipelineExecutor
{
    /**
     * {@inheritdoc}
     */
    public function execute(IConnection $connection, &$commands)
    {
        $connectionExceptions = array();
        $sizeofPipe = count($commands);
        $values = array();

        foreach ($commands as $command) {
            $cmdConnection = $connection->getConnection($command);

            if (isset($connectionExceptions[spl_object_hash($cmdConnection)])) {
                continue;
            }

            try {
                $cmdConnection->writeCommand($command);
            }
            catch (CommunicationException $exception) {
                $connectionExceptions[spl_object_hash($cmdConnection)] = $exception;
            }
        }

        for ($i = 0; $i < $sizeofPipe; $i++) {
            $command = $commands[$i];
            unset($commands[$i]);

            $cmdConnection = $connection->getConnection($command);
            $connectionObjectHash = spl_object_hash($cmdConnection);

            if (isset($connectionExceptions[$connectionObjectHash])) {
                $values[] = $connectionExceptions[$connectionObjectHash];
                continue;
            }

            try {
                $response = $cmdConnection->readResponse($command);
                $values[] = $response instanceof \Iterator ? iterator_to_array($response) : $response;
            }
            catch (ServerException $exception) {
                $values[] = $exception->toResponseError();
            }
            catch (CommunicationException $exception) {
                $values[] = $exception;
                $connectionExceptions[$connectionObjectHash] = $exception;
            }
        }

        return $values;
    }
}

/* --------------------------------------------------------------------------- */

namespace Predis\Iterators;

use Predis\Network\IConnection;
use Predis\Network\IConnectionSingle;

/**
 * Iterator that abstracts the access to multibulk replies and allows
 * them to be consumed by user's code in a streaming fashion.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
abstract class MultiBulkResponse implements \Iterator, \Countable
{
    protected $_position;
    protected $_current;
    protected $_replySize;

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        // NOOP
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->_current;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (++$this->_position < $this->_replySize) {
            $this->_current = $this->getValue();
        }

        return $this->_position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->_position < $this->_replySize;
    }

    /**
     * Returns the number of items of the whole multibulk reply.
     *
     * This method should be used to get the size of the current multibulk
     * reply without using iterator_count, which actually consumes the
     * iterator to calculate the size (rewinding is not supported).
     *
     * @return int
     */
    public function count()
    {
        return $this->_replySize;
    }

    /**
     * {@inheritdoc}
     */
    protected abstract function getValue();
}

/**
 * Abstracts the access to a streamable list of tuples represented
 * as a multibulk reply that alternates keys and values.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class MultiBulkResponseTuple extends MultiBulkResponse
{
    private $_iterator;

    /**
     * @param MultiBulkResponseSimple $iterator Multibulk reply iterator.
     */
    public function __construct(MultiBulkResponseSimple $iterator)
    {
        $virtualSize = count($iterator) / 2;
        $this->_iterator = $iterator;
        $this->_position = 0;
        $this->_current = $virtualSize > 0 ? $this->getValue() : null;
        $this->_replySize = $virtualSize;
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        $this->_iterator->sync();
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue()
    {
        $k = $this->_iterator->current();
        $this->_iterator->next();

        $v = $this->_iterator->current();
        $this->_iterator->next();

        return array($k, $v);
    }
}

/**
 * Streams a multibulk reply.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class MultiBulkResponseSimple extends MultiBulkResponse
{
    private $_connection;

    /**
     * @param IConnectionSingle $connection Connection to Redis.
     * @param int $size Number of elements of the multibulk reply.
     */
    public function __construct(IConnectionSingle $connection, $size)
    {
        $this->_connection = $connection;
        $this->_position   = 0;
        $this->_current    = $size > 0 ? $this->getValue() : null;
        $this->_replySize  = $size;
    }

    /**
     * Handles the synchronization of the client with the Redis protocol
     * then PHP's garbage collector kicks in (e.g. then the iterator goes
     * out of the scope of a foreach).
     */
    public function __destruct()
    {
        $this->sync();
    }

    /**
     * Synchronizes the client with the queued elements that have not been
     * read from the connection by consuming the rest of the multibulk reply,
     * or simply by dropping the connection.
     *
     * @param Boolean $drop True to synchronize the client by dropping the connection.
     *                      False to synchronize the client by consuming the multibulk reply.
     */
    public function sync($drop = false)
    {
        if ($drop == true) {
            if ($this->valid()) {
                $this->_position = $this->_replySize;
                $this->_connection->disconnect();
            }
        }
        else {
            while ($this->valid()) {
                $this->next();
            }
        }
    }

    /**
     * Reads the next item of the multibulk reply from the server.
     *
     * @return mixed
     */
    protected function getValue()
    {
        return $this->_connection->read();
    }
}

/* --------------------------------------------------------------------------- */

namespace Predis\Transaction;

use Predis\PredisException;
use Predis\Client;
use Predis\Helpers;
use Predis\ResponseQueued;
use Predis\ClientException;
use Predis\ServerException;
use Predis\CommunicationException;
use Predis\Protocol\ProtocolException;

/**
 * Client-side abstraction of a Redis transaction based on MULTI / EXEC.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class MultiExecContext
{
    const STATE_RESET       = 0x00000;
    const STATE_INITIALIZED = 0x00001;
    const STATE_INSIDEBLOCK = 0x00010;
    const STATE_DISCARDED   = 0x00100;
    const STATE_CAS         = 0x01000;
    const STATE_WATCH       = 0x10000;

    private $_state;
    private $_canWatch;

    protected $_client;
    protected $_options;
    protected $_commands;

    /**
     * @param Client Client instance used by the context.
     * @param array Options for the context initialization.
     */
    public function __construct(Client $client, Array $options = null)
    {
        $this->checkCapabilities($client);
        $this->_options = $options ?: array();
        $this->_client = $client;
        $this->reset();
    }

    /**
     * Sets the internal state flags.
     *
     * @param int $flags Set of flags
     */
    protected function setState($flags)
    {
        $this->_state = $flags;
    }

    /**
     * Gets the internal state flags.
     *
     * @return int
     */
    protected function getState()
    {
        return $this->_state;
    }

    /**
     * Sets one or more flags.
     *
     * @param int $flags Set of flags
     */
    protected function flagState($flags)
    {
        $this->_state |= $flags;
    }

    /**
     * Resets one or more flags.
     *
     * @param int $flags Set of flags
     */
    protected function unflagState($flags)
    {
        $this->_state &= ~$flags;
    }

    /**
     * Checks is a flag is set.
     *
     * @param int $flags Flag
     * @return Boolean
     */
    protected function checkState($flags)
    {
        return ($this->_state & $flags) === $flags;
    }

    /**
     * Checks if the passed client instance satisfies the required conditions
     * needed to initialize a transaction context.
     *
     * @param Client Client instance used by the context.
     */
    private function checkCapabilities(Client $client)
    {
        if (Helpers::isCluster($client->getConnection())) {
            throw new ClientException(
                'Cannot initialize a MULTI/EXEC context over a cluster of connections'
            );
        }

        $profile = $client->getProfile();

        if ($profile->supportsCommands(array('multi', 'exec', 'discard')) === false) {
            throw new ClientException(
                'The current profile does not support MULTI, EXEC and DISCARD'
            );
        }

        $this->_canWatch = $profile->supportsCommands(array('watch', 'unwatch'));
    }

    /**
     * Checks if WATCH and UNWATCH are supported by the server profile.
     */
    private function isWatchSupported()
    {
        if ($this->_canWatch === false) {
            throw new ClientException(
                'The current profile does not support WATCH and UNWATCH'
            );
        }
    }

    /**
     * Resets the state of a transaction.
     */
    protected function reset()
    {
        $this->setState(self::STATE_RESET);
        $this->_commands = array();
    }

    /**
     * Initializes a new transaction.
     */
    protected function initialize()
    {
        if ($this->checkState(self::STATE_INITIALIZED)) {
            return;
        }

        $options = $this->_options;

        if (isset($options['cas']) && $options['cas']) {
            $this->flagState(self::STATE_CAS);
        }
        if (isset($options['watch'])) {
            $this->watch($options['watch']);
        }

        $cas = $this->checkState(self::STATE_CAS);
        $discarded = $this->checkState(self::STATE_DISCARDED);

        if (!$cas || ($cas && $discarded)) {
            $this->_client->multi();
            if ($discarded) {
                $this->unflagState(self::STATE_CAS);
            }
        }

        $this->unflagState(self::STATE_DISCARDED);
        $this->flagState(self::STATE_INITIALIZED);
    }

    /**
     * Dinamically invokes a Redis command with the specified arguments.
     *
     * @param string $method Command ID.
     * @param array $arguments Arguments for the command.
     * @return MultiExecContext
     */
    public function __call($method, $arguments)
    {
        $this->initialize();
        $client = $this->_client;

        if ($this->checkState(self::STATE_CAS)) {
            return call_user_func_array(array($client, $method), $arguments);
        }

        $command  = $client->createCommand($method, $arguments);
        $response = $client->executeCommand($command);

        if (!$response instanceof ResponseQueued) {
            $this->onProtocolError('The server did not respond with a QUEUED status reply');
        }

        $this->_commands[] = $command;

        return $this;
    }

    /**
     * Executes WATCH on one or more keys.
     *
     * @param string|array $keys One or more keys.
     * @return mixed
     */
    public function watch($keys)
    {
        $this->isWatchSupported();

        if ($this->checkState(self::STATE_INITIALIZED) && !$this->checkState(self::STATE_CAS)) {
            throw new ClientException('WATCH after MULTI is not allowed');
        }

        $watchReply = $this->_client->watch($keys);
        $this->flagState(self::STATE_WATCH);

        return $watchReply;
    }

    /**
     * Finalizes the transaction on the server by executing MULTI on the server.
     *
     * @return MultiExecContext
     */
    public function multi()
    {
        if ($this->checkState(self::STATE_INITIALIZED | self::STATE_CAS)) {
            $this->unflagState(self::STATE_CAS);
            $this->_client->multi();
        }
        else {
            $this->initialize();
        }

        return $this;
    }

    /**
     * Executes UNWATCH.
     *
     * @return MultiExecContext
     */
    public function unwatch()
    {
        $this->isWatchSupported();
        $this->unflagState(self::STATE_WATCH);
        $this->_client->unwatch();

        return $this;
    }

    /**
     * Resets a transaction by UNWATCHing the keys that are being WATCHed and
     * DISCARDing the pending commands that have been already sent to the server.
     *
     * @return MultiExecContext
     */
    public function discard()
    {
        if ($this->checkState(self::STATE_INITIALIZED)) {
            $command = $this->checkState(self::STATE_CAS) ? 'unwatch' : 'discard';
            $this->_client->$command();
            $this->reset();
            $this->flagState(self::STATE_DISCARDED);
        }

        return $this;
    }

    /**
     * Executes the whole transaction.
     *
     * @return mixed
     */
    public function exec()
    {
        return $this->execute();
    }

    /**
     * Checks the state of the transaction before execution.
     *
     * @param mixed $callable Callback for execution.
     */
    private function checkBeforeExecution($callable)
    {
        if ($this->checkState(self::STATE_INSIDEBLOCK)) {
            throw new ClientException(
                "Cannot invoke 'execute' or 'exec' inside an active client transaction block"
            );
        }

        if ($callable) {
            if (!is_callable($callable)) {
                throw new \InvalidArgumentException(
                    'Argument passed must be a callable object'
                );
            }

            if (count($this->_commands) > 0) {
                $this->discard();
                throw new ClientException(
                    'Cannot execute a transaction block after using fluent interface'
                );
            }
        }

        if (isset($this->_options['retry']) && !isset($callable)) {
            $this->discard();
            throw new \InvalidArgumentException(
                'Automatic retries can be used only when a transaction block is provided'
            );
        }
    }

    /**
     * Handles the actual execution of the whole transaction.
     *
     * @param mixed $callable Callback for execution.
     * @return array
     */
    public function execute($callable = null)
    {
        $this->checkBeforeExecution($callable);

        $reply = null;
        $returnValues = array();
        $attemptsLeft = isset($this->_options['retry']) ? (int)$this->_options['retry'] : 0;

        do {
            if ($callable !== null) {
                $this->executeTransactionBlock($callable);
            }

            if (count($this->_commands) === 0) {
                if ($this->checkState(self::STATE_WATCH)) {
                    $this->discard();
                }
                return;
            }

            $reply = $this->_client->exec();

            if ($reply === null) {
                if ($attemptsLeft === 0) {
                    $message = 'The current transaction has been aborted by the server';
                    throw new AbortedMultiExecException($this, $message);
                }

                $this->reset();

                if (isset($this->_options['on_retry']) && is_callable($this->_options['on_retry'])) {
                    call_user_func($this->_options['on_retry'], $this, $attemptsLeft);
                }

                continue;
            }

            break;
        } while ($attemptsLeft-- > 0);

        $execReply = $reply instanceof \Iterator ? iterator_to_array($reply) : $reply;
        $sizeofReplies = count($execReply);
        $commands = $this->_commands;

        if ($sizeofReplies !== count($commands)) {
            $this->onProtocolError("EXEC returned an unexpected number of replies");
        }

        for ($i = 0; $i < $sizeofReplies; $i++) {
            $commandReply = $execReply[$i];

            if ($commandReply instanceof \Iterator) {
                $commandReply = iterator_to_array($commandReply);
            }

            $returnValues[$i] = $commands[$i]->parseResponse($commandReply);
            unset($commands[$i]);
        }

        return $returnValues;
    }

    /**
     * Passes the current transaction context to a callable block for execution.
     *
     * @param mixed $callable Callback.
     */
    protected function executeTransactionBlock($callable)
    {
        $blockException = null;
        $this->flagState(self::STATE_INSIDEBLOCK);

        try {
            $callable($this);
        }
        catch (CommunicationException $exception) {
            $blockException = $exception;
        }
        catch (ServerException $exception) {
            $blockException = $exception;
        }
        catch (\Exception $exception) {
            $blockException = $exception;
            $this->discard();
        }

        $this->unflagState(self::STATE_INSIDEBLOCK);

        if ($blockException !== null) {
            throw $blockException;
        }
    }

    /**
     * Helper method that handles protocol errors encountered inside a transaction.
     *
     * @param string $message Error message.
     */
    private function onProtocolError($message)
    {
        // Since a MULTI/EXEC block cannot be initialized over a clustered
        // connection, we can safely assume that Predis\Client::getConnection()
        // will always return an instance of Predis\Network\IConnectionSingle.
        Helpers::onCommunicationException(new ProtocolException(
            $this->_client->getConnection(), $message
        ));
    }
}

/**
 * Exception class that identifies MULTI / EXEC transactions aborted by Redis.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class AbortedMultiExecException extends PredisException
{
    private $_transaction;

    /**
     * @param MultiExecContext $transaction Transaction that generated the exception.
     * @param string $message Error message.
     * @param int $code Error code.
     */
    public function __construct(MultiExecContext $transaction, $message, $code = null)
    {
        parent::__construct($message, $code);

        $this->_transaction = $transaction;
    }

    /**
     * Returns the transaction that generated the exception.
     *
     * @return MultiExecContext
     */
    public function getTransaction()
    {
        return $this->_transaction;
    }
}

/* --------------------------------------------------------------------------- */

