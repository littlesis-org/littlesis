<?php

pake_desc('promote a user super administrator');
pake_task('promote-super-admin', 'project_exists');

function run_promote_super_admin($task, $args)
{
  if (!count($args))
  {
    throw new Exception('You must provide a user name.');
  }

  $username = $args[0];

  throw new Exception('This task is not yet implemented.');
}
