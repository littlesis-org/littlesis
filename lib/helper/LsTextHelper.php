<?php

function excerpt($string, $len=50, $truncateStr='...', $wholeWords=true)
{
  return LsString::excerpt($string, $len, $truncateStr, $wholeWords);
}

function first_html_paragraph($str, $truncateStr='...')
{
  preg_match('/^(.*)<br \/>/U', $str, $shorter);
  if ($shorter)
  {
    $excerpt = $shorter[1];
    
    if ($excerpt != $str)
    {
      $excerpt .= $truncateStr;
    }
  }
  else
  {
    $excerpt = $str;
  }
  
  return $excerpt;
}

function first_paragraph($str, $truncateStr='...')
{
  preg_match('/^(.*)\n/U', $str, $shorter);
  $excerpt = $shorter[1];
  
  if ($excerpt != $str)
  {
    $excerpt .= $truncateStr;
  }
  
  return $excerpt;
}

function highlight_orgs($str, $class='highlight')
{
  $str = preg_replace('/(?<!School) (of|of the|at|its|joined|with) (?!Board|Directors)([\p{Lu}].*)(?!\sof)(?=\.\s\p{Lu}|\s\(?\p{Ll})/U', ' \1 <span class="' . $class . '">\2</span>', $str);
  return $str;
}

function highlight_matches($text, $strs, $format = '<strong>%s</strong>')
{
  $strs = array_filter($strs, 'multichar_filter_callback');
  $pattern = '/(' . implode('|', $strs) . ')/i';
  $replacement = sprintf($format, '\0');
  $text = preg_replace($pattern, $replacement, $text);
  
  return $text;
}

function multichar_filter_callback($str)
{
  return strlen($str) > 1;
}

function am() 
{
  $r = array();
  foreach (func_get_args()as $a) {
    if (!is_array($a)) {
      $a = array($a);
    }
    $r = array_merge($r, $a);
  }
  return $r;
}


function sortByKey(&$array, $sortby, $order = 'asc', $type = SORT_NUMERIC) {
  if (!is_array($array)) {
    return null;
  }

  foreach ($array as $key => $val) {
    $sa[$key] = $val[$sortby];
  }

  if ($order == 'asc') {
    asort($sa, $type);
  } else {
    arsort($sa, $type);
  }

  foreach ($sa as $key => $val) {
    $out[] = $array[$key];
  }
  return $out;
}



//From CakePHP's SummaryHelper
function excerpt_matches($text, $words, $length=100, $prefix="...", $suffix = null, $options = array()) {

  // Set default score modifiers [tweak away...]
  $options = am(array(
    'exact_case_bonus'  => 2,
    'exact_word_bonus'  => 3,
    'abs_length_weight' => 0.0,
    'rel_length_weight' => 1.0,

    'debug' => false
  ), $options);

  // Null suffix defaults to same as prefix
  if (is_null($suffix)) {
    $suffix = $prefix;
  }

  // Not enough to work with?
  if (strlen($text) <= $length) {
    return $text;
  }

  // Just in case
  if (!is_array($words)) {
    $words = array($words);
  }

  // Build the event list
  // [also calculate maximum word length for relative weight bonus]
  $events = array();
  $maxWordLength = 0;

  foreach ($words as $word) {

    if (strlen($word) > $maxWordLength) {
      $maxWordLength = strlen($word);
    }

    $i = -1;
    while ( ($i = stripos($text, $word, $i+1)) !== false ) {

      // Basic score for a match is always 1
      $score = 1;

      // Apply modifiers
      if (substr($text, $i, strlen($word)) == $word) {
        // Case matches exactly
        $score += $options['exact_case_bonus'];
      }
      if ($options['abs_length_weight'] != 0.0) {
        // Absolute length weight (longer words count for more)
        $score += strlen($word) * $options['abs_length_weight'];
      }
      if ($options['rel_length_weight'] != 0.0) {
        // Relative length weight (longer words count for more)
        $score += strlen($word) / $maxWordLength * $options['rel_length_weight'];
      }
      if (preg_match('/\W/', substr($text, $i-1, 1))) {
        // The start of the word matches exactly
        $score += $options['exact_word_bonus'];
      }
      if (preg_match('/\W/', substr($text, $i+strlen($word), 1))) {
        // The end of the word matches exactly
        $score += $options['exact_word_bonus'];
      }

      // Push event occurs when the word comes into range
      $events[] = array(
        'type'  => 'push',
        'word'  => $word,
        'pos'   => max(0, $i + strlen($word) - $length),
        'score' => $score
      );
      // Pop event occurs when the word goes out of range
      $events[] = array(
        'type' => 'pop',
        'word' => $word,
        'pos'  => $i + 1,
        'score' => $score
      );
      // Bump event makes it more attractive for words to be in the
      // middle of the excerpt [@todo: this needs work]
      $events[] = array(
        'type' => 'bump',
        'word' => $word,
        'pos'  => max(0, $i + floor(strlen($word)/2) - floor($length/2)),
        'score' => 0.5
      );

    }
  }

  // If nothing is found then just truncate from the beginning
  if (empty($events)) {
    return '';
  }

  // We want to handle each event in the order it occurs in
  // 
  $events = sortByKey($events, 'pos');

  $scores = array();
  $score = 0;
  $current_words = array();

  // Process each event in turn
  foreach ($events as $idx => $event) {
    $thisPos = floor($event['pos']);

    $word = strtolower($event['word']);

    switch ($event['type']) {
    case 'push':
      if (empty($current_words[$word])) {
        // First occurence of a word gets full value
        $current_words[$word] = 1;
        $score += $event['score'];
      }
      else {
        // Subsequent occurrences mean less and less
        $current_words[$word]++;
        $score += $event['score'] / sizeof($current_words[$word]);
      }
      break;
    case 'pop':
      if (($current_words[$word])==1) {
        unset($current_words[$word]);
        $score -= ($event['score']);
      }
      else {
        $current_words[$word]--;
        $score -= $event['score'] / sizeof($current_words[$word]);
      }
      break;
    case 'bump':
      if (!empty($event['score'])) {
        $score += $event['score'];
      }
      break;
    default:
    }

    // Close enough for government work...
    $score = round($score, 2);

    // Store the position/score entry
    $scores[$thisPos] = $score;

    // For use with debugging
    $debugWords[$thisPos] = $current_words;

    // Remove score bump
    if ($event['type'] == 'bump') {
        $score -= $event['score'];
    }
  }

  // Calculate the best score
  // Yeah, could have done this in the main event loop
  // but it's better here
  $bestScore = 0;
  foreach ($scores as $pos => $score) {
      if ($score > $bestScore) {
        $bestScore = $score;
      }
  }


  if ($options['debug']) {
    // This is really quick, really tatty debug information
    // (but it works)
    echo "<table border>";
    echo "<caption>Events</caption>";
    echo "<tr><th>Pos</th><th>Type</th><th>Word</th><th>Score</th>";
    foreach ($events as $event) {
      echo "<tr>";
      echo "<td>{$event['pos']}</td><td>{$event['type']}</td><td>{$event['word']}</td><td>{$event['score']}</td>";
      echo "</tr>";
    }
    echo "</table>";

    echo "<table border>";
    echo "<caption>Positions and their scores</caption>";
    $idx = 0;
    foreach ($scores as $pos => $score) {
      $excerpt = substr($text, $pos, $length);
      $style = ($score == $bestScore) ? 'background: #ff7;' : '';

      //$score = floor($score + 0.5);

      echo "<tr>";
      echo "<th style=\"$style\">" . $idx . "</th>";
      echo "<td style=\"$style\">" . $pos . "</td>";
      echo "<td style=\"$style\"><div style=\"float: left; width: 2em; margin-right: 1em; text-align right; background: #ddd\">" . $score . "</div><code>" . str_repeat('*', $score) . "</code></td>";
      echo "<td style=\"$style\"><table border>";
      foreach ($debugWords[$pos] as $word => $count) {
        echo "<tr><td>$word</td><td>$count</td></tr>";
      }
      echo "</table></td>";
      echo "<td style=\"$style\">" . (preg_replace('/(' . implode('|', $words) . ')/i', '<b style="border: 1px solid red;">\1</b>', htmlentities($excerpt))) . "</td>";
      echo "</tr>";
      $idx++;
    }
    echo "</table>";
  }


  // Find all positions that correspond to the best score
  $positions = array();
  foreach ($scores as $pos => $score) {
    if ($score == $bestScore) {
      $positions[] = $pos;
    }
  }

  if (sizeof($positions) > 1) {
    // Scores are tied => do something clever to choose one
    // @todo: Actually do something clever here
    $pos = $positions[0];
  }
  else {
    $pos = $positions[0];
  }

  // Extract the excerpt from the position, (pre|ap)pend the (pre|suf)fix
  $excerpt = substr($text, $pos, $length);

  $beginPos = strpos($excerpt, ' ') + 1;
  $endPos = strrpos($excerpt, ' ');
  $excerpt = substr($excerpt, $beginPos, $endPos-$beginPos);

  if ($pos > 0) {
    $excerpt = $prefix . $excerpt;
  }
  if ($pos + $length < strlen($text)) {
    $excerpt .= $suffix;
  }  

  return $excerpt;
}


?>