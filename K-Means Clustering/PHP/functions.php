<?php
/**
 * This is a file containing commonly used sanitization / error handling / and other miscellaneous functions
 */
// -------------------------------------------------------------------------------------------------------------
/**
 * Sanitizes user input to prevent against XSS and MySQL injection.
 */
function fix_string($conn,$string)
{
    return htmlentities(my_sql_fix_string($conn,$string));
}


/**
 * Helper function to help sanitize user input to prevent MySQL injection.
 */
function my_sql_fix_string($conn, $string)
{
    if (get_magic_quotes_gpc())
    {
        $string = stripslashes($string);
    }
    return $conn->real_escape_string($string);
}


/**
 * Prints out a generic message upon an encountered connection error.
 */
function my_sql_fatal_error()
{
    return "We are very sorry, but we were unable to complete your request. Please try again.";
}


/**
 * Inserts html code into a specified div utilizing jQuery and AJAX.
 */
function insertHTML($targetPage,$targetDiv,$newHTML)
{
    echo <<< _FINISH
    <html>
        <script>
            $.ajax({url:"$targetPage", success: function(){
                $("$targetDiv").append("<p>$newHTML</p>");
            }});
        </script>
    <html>
_FINISH;
}


/**
 * Checks whether an account with a user's provided credentials (specfically checks for duplicate emails and usernames) is already in use.
 */
function check_for_duplicate_credential($conn,$field,$credential)
{
    $credentialAlreadyExists = false;
    $statement = $conn->prepare("SELECT $credential FROM users WHERE $credential=?");
    $statement->bind_param('s', $field);
    $statement->execute();

    $result = $statement->get_result();
    $rows = $result->num_rows;

    for ($i = 0; $i < $rows; $i++)
    {
        $result->data_seek($i);
        $row = $result->fetch_array(MYSQLI_NUM);
        if ($field == $row[0])
            $credentialAlreadyExists = true;
    }
    $statement->close();
    return $credentialAlreadyExists;
}

?>