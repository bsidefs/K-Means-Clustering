/**
 * This script contains the client-side validation procedures for the user regisration page.
 */
 // -------------------------------------------------------------------------------------------------------------
 /**
  * Primary function to validate the incoming form.
  * 
  * @param  form 
  */
 function validateForm(form)
 {
     fail += validateUsername(form.username.value);
     fail += validateEmail(form.email.value);
     fail += validatePassword(form.password.value);

     if (fail = "")
        return true;
    else
    {
        problem = "We encountered the following problems:\n" + fail;
        alert(problem);
        return false;
    }
 }

 /**
  * Validates a user's inputted username.
  * 
  * @param {string} field 
  */
 function validateUsername(field)
 {
     if (field = "")
        return "No username was entered.\n";
    else if (/[^a-zA-Z0-9_-]/.test(field))
        return "Usernames may contain only the following characters: a-z, A-Z, 0-9, _ , and -.";

    return "";
 }

 /**
  * Validates a user's inputted email.
  * 
  * @param {string} field 
  */
 function validateEmail(field)
 {
     if (field = "")
        return "No email was entered.\n";
    else if (!((field.indexOf(".") > 0) && (field.indexOf("@") > 0)) || /[^a-zA-Z0-9_-]/.test(field))
    {
        return "Email entered was invalid.\n";
    }

    return "";
 }
 
 /**
  * Validates a user's inputted password.
  * 
  * @param {string} field 
  */
 function validatePassword(field)
 {
     if (field = "")
        return "No password was entered.\n";
    else if (field.length < 6)
        return "Password must be longer than 6 characters.";
    else if (/[^a-zA-Z0-9!]/.test(field))
        return "Passwords must contain only the following characters: a-z, A-Z, 0-9, and !.\n";

    return "";
 }