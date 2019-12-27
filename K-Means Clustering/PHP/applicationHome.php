<?php
/**
 * Contains the html information for the application's "logged in" home page. 
 */

 require_once "loginCredentials.php";
 require_once "functions.php";
 require_once "KMeans.php";
 require_once "Cluster.php";

 $conn = new mysqli($hn,$un,$pw,$db);
 if ($conn->connect_error) die(my_sql_fatal_error());
 // used to hold the values for the k-means algorithm // easy way out for just right here and or and or right here and or and right now
 $scores_to_input = array();
 $ready_to_test = false;

 // setting a max timeout on the session here
 ini_set("session.gc_maxlifetime", (60*60*24));

 // resuming the session started immediately after login
 session_start();

 // security check to help prevent session fixation
 if (!isset($_SESSION["initiated"]))
 {
    // if the above value has not been set, then regenerate the session id
    session_regenerate_id();
    $_SESSION["initiated"] = true;
 }

 // checking if the Session superglobal was populated (meaning the user was logged in) and adding an additional security
 // check to help prevent session hijacking
 if (isset($_SESSION["username"]) && 
        $_SESSION["check"] == hash("ripemd128", $_SERVER["REMOTE_ADDR"].$_SERVER["HTTP_USER_AGENT"])) // additional security check
 {
    $username = $_SESSION["username"];

    // did not destroy the session here (unlike the example in class), because this script will have to re-run once the form down 
    // below in the functional version is submitted. the function is called, however, at least once within the script (later on).

    // if the second check, more specifically, passed, then the user is safe and we can display the appropriate page contents
    display_functional_application($username);

    // upon the page reload, check for the contents of the form submission

    // code for handling "training" uploads
    if (isset($_FILES["train-model-file"]) && isset($_POST["train-model-name"]))
    {
        $fileName = $_FILES["train-model-file"]["name"];
        $fileType = $_FILES["train-model-file"]["type"];
        if (check_file_integrity($fileType))
        {
            $modelScores = fix_string($conn,file_get_contents($fileName));
            $modelName = fix_string($conn,$_POST["train-model-name"]);

            $statement = $conn->prepare("INSERT INTO userModels VALUES(?,?,?)");
            $statement->bind_param('sss',$username,$modelName,$modelScores);

            $statement->execute();
            if (!($statement->affected_rows) > 0)
            {
                echo my_sql_fatal_error();
                $statement->close();
            }
            else
            {
                $statement->close();
                $ready_to_test = true;
            }
        }
    }
    if (isset($_POST["train-model-scores"]) && isset($_POST["train-model-name"]))
    {
        $modelScores = fix_string($conn,$_POST["train-model-scores"]);
        $scores_to_input = preg_split("/[,]/",$modelScores);
        $modelName = fix_string($conn,$_POST["train-model-name"]);
        $statement = $conn->prepare("INSERT INTO userModels VALUES(?,?,?)");
        $statement->bind_param('sss',$username,$modelName,$modelScores);

        $statement->execute();
        if (!($statement->affected_rows) > 0)
            {
                echo my_sql_fatal_error();
                $statement->close();
            }
            else
            {
                $statement->close();
                $ready_to_test = true;
            }
    }

    // code for handling "testing" uploads

    //  --------------- code for performing the k-means based clustering algorithm ---------------------------------
    if ($ready_to_test)
    {
        $kMeans = new KMeans($scores_to_input,2);
        $kMeans->initialize_clusters();
        $kMeans->perform_k_means();
        $kMeans->write_solution_to_file();
    }

    // -------------------- end of the code for performing the k-means based clustering algorithm  -------------------------------

 }
 else
 {
    // display a message telling the user to login to make use of the website
    display_non_functional_application();
    // destroy the session
    destory_session_and_data();
 }

 // closing the now finished MySQL connection
 $conn->close();

 /**
  * Checks whether an uploaded file was of the appropriate type.
  */
 function check_file_integrity($fileType)
 {
    if ($fileType != "text/plain")
        return false;
    else
        return true;
 }

 /**
 * A handy function to destroy a PHP Session and its data.
 */
function destory_session_and_data()
{
    // emptying out the session superglobal's contents
    $_SESSION = array();
    // deleting the cookie set up by the session starting
    setcookie(session_name(),'',time() - 2592000,'/');
    // destroying the session
    session_destroy();
}

/**
 * Displays a functional version of the application's homepage when the user is logged in.
 */
function display_functional_application($username)
{
    echo <<< _FINISH
        <html>
                <head>
                    <title>K-Means Based Clustering Web Application</title>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link href="applicationHome.css"  type="text/css" rel="stylesheet" />
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

                    <!-- show proper train/test functionalities-->
                    <script>
                        $(document).ready(function()
                        {
                            $(".train").hide();
                            $(".test").hide();
                            $(".submission-area label").hide();
                            $(".content p").hide();
                            var radioButtons = $("input[type='radio']");
                            radioButtons.on("change", function()
                            {
                                switch($(this).attr("id"))
                                {
                                    case "training": (function()
                                    {
                                        $(".submission-area label").hide();
                                        $(".test").hide();
                                        $(".content p").fadeIn(1050);
                                        $(".train").fadeIn(1050);
                                        $(".submission-area label").fadeIn(1050);
                                    }());
                                        break;
                                    case "testing": (function()
                                    {
                                        $(".submission-area label").hide();
                                        $(".train").hide();
                                        $(".test").fadeIn(1050);
                                        $(".submission-area label").fadeIn(1050);
                                    }());
                                }
                            });
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
                                        <li><a href="loginPage.php">Login</a></li>
                                        <li><a href="registerPage.php">Register</a></li>
                                        <li>|</li>
                                        <li><a href="homePage.php" id="log-out">Log Out</a></li>
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
                                    <h1>Welcome $username.</h1>
                                </div> 
                                <div class="description">
                                    <form action="applicationHome.php" method="post" enctype="multipart/form-data">
                                        <fieldset>
                                            <div class="centralized-content">
                                                <input id="training" type="radio" name="user-option" value="Train" autocomplete="off">
                                                <label id="train-label" for="training">Train</label>
                                                <input id="testing" type="radio" name="user-option" value="Test" autocomplete="off">
                                                <label id="test-label" for="testing">Test</label>
                                            </div>
                                            <div class="functionality">
                                                <div class="train">
                                                    <input id="train-file-uploader" type="file" name="train-model-file">
                                                    <label class="uploader-wrapper" for="train-file-uploader"><i class="far fa-file-alt"></i> Upload a Model</label>
                                                    <input type="text" name="train-model-scores" placeholder="input scores">
                                                    <input type="text" name="train-model-name" placeholder="model name">
                                                </div>
                                                <div class="test">
                                                    <input id="test-file-uploader" type="file" name="test-model-file">
                                                    <label class="uploader-wrapper" for="test-file-uploader"><i class="far fa-file-alt"></i> Upload a Model</label>
                                                </div>

                                                <div class="submission-area">
                                                    <label class="model-submit" for="arrow"><i class="far fa-arrow-alt-circle-right fa-3x"></i></label>
                                                    <input type="submit" id="arrow" value="">
                                                </div>
                                            </div>
                                        </fieldset>
                                    </form>
                                    <div class="content">
                                        <p id="disclaimer">*Please separate the model scores by commas, whether in the file upload or in the input box.</p>
                                    </div>
                                        <div class="insertedText">
                                        </div>
                                </div>
                            </div> <!-- end of the "main-content" container-->
                        </section>
                        <!-- end of home or landing page section-->

                    </div> 
                    <!-- end of the "main" container-->
                </body>
            </html>
_FINISH;
}

/**
 * Displays a non-functional version of the application's homepage when the page is navigated to prior to logging in.
 */
function display_non_functional_application()
{
    echo <<< _FINISH
        <html>
                <head>
                    <title>K-Means Based Clustering Machine Learning Algorithm</title>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link href="applicationHome.css"  type="text/css" rel="stylesheet" />
                    <link rel="stylesheet" href="https://use.typekit.net/nar5czb.css">
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
                                        <li><a href="">Home</a></li>
                                        <li><a href="loginPage.php">Login</a></li>
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
                                    <h1>Please login to access this page.</h1>
                                </div> 
                                <div class="description">
                                </div>
                            </div> <!-- end of the "main-content" container-->
                        </section>
                        <!-- end of home or landing page section-->

                    </div> 
                    <!-- end of the "main" container-->
                </body>
            </html>
_FINISH;
}

?>