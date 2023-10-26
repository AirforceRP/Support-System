<?php

// Function to check if PDO extension is loaded
function isPdoLoaded()
{
    return extension_loaded('pdo');
}

// Function to check if PHP version is 7.3 or higher
function isPhpVersionValid()
{
    return version_compare(phpversion(), '7.3', '>=');
}

// Function to generate the requirements table
function generateRequirementsTable()
{
    $requirements = [
        'PHP Version' => ['7.3 or higher', isPhpVersionValid()],
        'PDO Extension' => ['Installed', isPdoLoaded()],
        // Add more requirements as needed
    ];

    echo '<table border="1">';
    echo '<tr><th>Requirement</th><th>Minimum Version</th><th>Status</th></tr>';

    foreach ($requirements as $requirement => [$minimumVersion, $status]) {
        echo "<tr><td>$requirement</td><td>$minimumVersion</td><td>";
        echo $status ? '<span style="color: green;">OK</span>' : '<span style="color: red;">Not Met</span>';
        echo '</td></tr>';
    }

    echo '</table>';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requirements</title>
</head>
<body>

    <h1>System Requirements</h1>

    <?php
    generateRequirementsTable();
    ?>

    <p>Note: Adjust the PHP version and extension requirements as needed for your project.</p>
  <a href="installer.php">install</a>

</body>
</html>
