function parseHash()
{
  var hash = window.location.hash;
  hash = hash.replace(/^#/g, 'action:');
  hash = hash.replace(/\|/g, '&');
  hash = hash.replace(/\:/g, '=');

  // lets turn our url hash into a javascript hash (like an associated array)
  // we will use a useful function provided in the prototype library
  hash = hash.parseQuery();

  //if (!hash.action) { hash.action = 'relationships'; }

  return hash;
}