<?php

function readable_number($num, $prefix=null)
{
	return LsNumber::makeReadable($num, $prefix);
}
