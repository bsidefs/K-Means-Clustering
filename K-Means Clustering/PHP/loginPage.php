<?php 
/**
 * Contains the html information for the login page of the web application.
 */
require_once "loginCredentials.php";
require_once "functions.php";
// establishing the connection with MySQL
$conn = new mysqli($hn,$un,$pw,$db);
if ($conn->connect_error) die(my_sql_fatal_error());

// calling the log in user function
log_in_user($conn);

/**
 * Upon valid credentials, securely logs a user into their associated web application account.
 */
function log_in_user($conn)
{
    if (isset($_POST["username"]) && isset($_POST["password"]))
    {
        // if the login credentials are set, then sanitize the user's input
        $username = fix_string($conn,$_POST["username"]);
        $password = fix_string($conn,$_POST["password"]);
        // preparing and executing the query
        $statement = $conn->prepare("SELECT salt,password FROM users WHERE username=?");
        $statement->bind_param('s',$username);
        $statement->execute();

        // checking to ensure that the query was executed properly
        if (!($statement->affected_rows) > 0)
            echo my_sql_fatal_error();
        // if the query was successful, then check that the user's provided password was correct
        else
        {
            // matching the user's provided password against the one stored in the database
            $statement->bind_result($userSpecificSalt,$userSpecificPassword);
            $statement->fetch();
            $token = hash("ripemd128", $userSpecificSalt.$password);

            if ($token == $userSpecificPassword)
            {
                // upon a successful user login, start a session
                session_start();
                // storing the items in the superglobal for later use
                $_SESSION["username"] = $username;
                $_SESSION["check"] = hash("ripemd128",$_SERVER["REMOTE_ADDR"].$_SERVER["HTTP_USER_AGENT"]);

                $statement->close();

                // closing the now finished MySQL connection before the website page redirects
                $conn->close();
                
                echo <<< _FINISH
                <html>
                    <script>
                        window.location.replace("applicationHome.php");
                    </script>
                </html>
_FINISH;
            }
            else
            {
                insertHTML("loginPage.php",".insertedText","&#8594; We are sorry, but the username/password combination you provided was incorrect. Please try again.");
                $statement->close();
                $conn->close();
            }
        }

    }
}

// outputting the html webpage
echo <<< _FINISH
    <html>
        <head>
            <title>K-Means Based Clustering Web Application</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="loginPage.css"  type="text/css" rel="stylesheet" />
            <link rel="stylesheet" href="https://use.typekit.net/nar5czb.css">
            <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

            <!-- activate link script-->
            <script>
                $(document).ready(function()
                {
                    var navLinks = $(".nav .navlinks a");
                    navLinks.click(function()
                    {
                        var selectedLink = $(this);
                        activateLink(selectedLink);
                    })

                    function activateLink(selectedLink)
                    {
                        $(".nav .navlinks a.active").removeClass("active");
                        selectedLink.addClass("active");
                    }
                });
            </script>
        </head>
        <body>
            <!-- main container-->
            <div class="container">

                <header>
                    <a id="top"></a>
                    <div class="nav">

                        <div class="left-side-title">
                            <h1>CS 174</h1>
                        </div>
                        

                        <div class="navlinks">
                            <ul>
                                <li><a href="homePage.php">Home</a></li>
                                <li><a href="" class="active">Login</a></li>
                                <li><a href="registerPage.php">Register</a></li>
                            </ul>
                        </div>


                        <div class="right-side-title">
                            <h1>Brian Tamsing</h1>
                        </div>
                    </div> <!-- end of the "navbar titles" container-->
                </header>

                <!-- home or landing page section-->
                <section id="landing-page">
                    <div class="container">
                        <div class="landing-title">
                            <h1>Login</h1>
                        </div> 
                        <div class="description">
                            <form action="loginPage.php" method="post" onsubmit="">
                                <fieldset>
                                    <input type="text" name="username" placeholder="username">
                                    <input type="password" name="password" placeholder="password">

                                    <div class="submission-area">
                                        <label for="arrow"><i class="far fa-arrow-alt-circle-right fa-3x"></i></label>
                                        <input type="submit" id="arrow" value="">
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                        <div class="insertedText">
                        </div>
                    </div> <!-- end of the "main-content" container-->
                </section>
                <!-- end of home or landing page section-->

            </div> 
            <!-- end of the "main" container-->
        </body>
    </html>
_FINISH;

?>
