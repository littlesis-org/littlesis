<?php

require_once("District.php");

// Initialize the District library
$district_object = new District();

// Get the SimpleXML object
$x = $district_object->getByOfficeState(8, 'PA');

echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- pwned -->
<html lang="en">
<body>';

// Check and make sure there is no error

if (isset($x->errorMessage)) { // If there is, let's handle it
        
        echo '
        <div>' . $x->errorMessage . '</div>';
        
} else { // If not, let's go ->
        
        echo '
        <div>
                <a href="' . $x->generalInfo->linkBack . '">' . $x->generalInfo->title . '</a>
        </div>
        <br /><br />
        <table>
                <tr>
                        <td>ID</td>
                        <td>Name</td>
                        <td>Office ID</td>
                        <td>State ID</td>
                </tr>
                ';
        
        // Since there are multiple districts, $x->district is actually an
        // array of other objects that we need to iterate through
        foreach ($x->district as $district) {
                
                echo '
                <tr>
                        <td>' . $district->id . '</td>
                        <td>' . $district->name . '</td>
                        <td>' . $district->officeId . '</td>
                        <td>' . $district->stateId . '</td>
                </tr>';
                
        }
        
}

echo '
</body>
</html>';

?>