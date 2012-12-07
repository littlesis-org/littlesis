<?php echo javascript_tag("

function selectEntity(entityLink, name)
{
  document.getElementById(name + '_results').innerHTML = '';
  document.getElementById(name + '_input').value = '';
  document.getElementById(name + '_search').style.display = 'none';

  var editLink = '<a href=\"javascript:void(0);\" onclick=\"changeEntity(\'' + name + '\');\">change</a>';

  document.getElementById(name + '_link').innerHTML = entityLink + ' ' + editLink;
  document.getElementById(name + '_link').style.display = 'block';
}

function changeEntity(name)
{
  document.getElementById(name + '_link').style.display = 'none';
  document.getElementById(name + '_link').innerHTML = '';
  
  document.getElementById(name + '_search').style.display = 'block';
}

") ?>