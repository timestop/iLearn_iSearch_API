﻿<?php
///////////////////////////////////////////////////
//editDepartment.php
//
//1.get information of the department to be edit
//2.update department name
//3.return string of the refreshed department table
//
// #001, 20130503 by Odie
//       update the department name, also need to update column "dept_list" in table "userLogin"
///////////////////////////////////////////////////

   define(FILE_NAME, "/usr/local/www/apache22/DB.conf");  //account file name
   define(DELAY_SEC, 3);
   define(FILE_ERROR, -2);
   
   if (file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo FILE_ERROR;
      return;
   }
   
   session_start();
   if (!session_is_registered("GUID"))  //check session
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   if ($_SESSION["GUID"] == "")
   {
      session_write_close();
      sleep(DELAY_SEC);
      header("Location:main.php");
      exit();
   }
   $GUID = $_SESSION["GUID"];
   session_write_close();
   
   header('Content-Type:text/html;charset=utf-8');
   
   //define
   define(REFRESH_PATH, "$working_path/refreshDepartPage.php");  //refresh page function
   define(DB_HOST, $db_host);
   define(ADMIN_ACCOUNT, $admin_account);
   define(ADMIN_PASSWORD, $admin_password);
   define(CONNECT_DB, $connect_db);
   define(ILLEGAL_CHAR, "'-;<>");                         //illegal char
   define(STR_LENGTH, 50);
  
   //return value
   define(SUCCESS, 0);
   define(DB_ERROR, -1);
   define(SYMBOL_ERROR, -3);
   define(SYMBOL_ERROR_CMD, -4);
   define(ERROR_SAME_NAME, -5);
   
   //check name
   function check_name($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > STR_LENGTH)
      {         
         return SYMBOL_ERROR;
      }
      //----- check empty string -----
      if(trim($check_str) == "")
      {
         return SYMBOL_ERROR;
      }       
      //----- replace "<" to "&lt" -----
      if(strpbrk($check_str, "<") == true)
      {
         $check_str = str_replace("<", "&lt;", $check_str);
      }
      //----- replace ">" to "&gt" -----
      if(strpbrk($check_str, ">") == true)
      {
         $check_str = str_replace(">", "&gt;", $check_str);
      }
      return $check_str;
   }
   
   //check command
   function check_command($check_str)
   {
      if(strcmp($check_str, "edit_dep"))
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   
   //----- Check number -----
   function check_number($check_str)
   {
      if(!is_numeric($check_str))
      {
        return SYMBOL_ERROR; 
      }
      return $check_str;
   }
   
   //get data from client
   $cmd;
   $departName;
   $departID;

   //query
   $link;
   $str_query;
   $str_update;
   $result;                 //query result
   $row;                    //1 data array
   $refresh_str;
   
   //depart
   $depID;
   $dep_name;
   
   //1.get information of the department to be edit  
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }

   if(($departName = check_name($_GET["departName"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   
   if(($departID = check_number($_GET["departID"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   // #001, add the parameter
   if(($oldDepartName = check_name($_GET["oldDepartName"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
      
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }

   //escape
   if (!get_magic_quotes_gpc()) 
   {
      $departNameQuote = "'". $departName. "'";
      $oldDepartNameQuote = "'". $oldDepartName. "'";
      $departName = mysql_real_escape_string($departName);
      $departNameQuote = mysql_real_escape_string($departNameQuote);
      $oldDepartNameQuote = mysql_real_escape_string($oldDepartNameQuote);
   } 
              
   //----- query -----
   $str_query = "
      select *
      from department
      where GUID = '" . $GUID . "' and dep_name = '" . $departName . "'";

   $str_update = "
      update department
      set dep_name = '" . $departName . 
      "'where GUID = '" . $GUID . "' and depID = $departID";

   $str_update_2 = "
      update userLogin
      set dept_list = replace(dept_list,'" . $oldDepartNameQuote.  "','". $departNameQuote .
      "') where GUID = '". $GUID . "'";
   //echo $str_update_2;
   //return;
   
   //2.update department name
   //----- Connect to MySql ----- 

   if ($result = mysqli_query($link, $str_query))
   {
      if(($row_number = mysqli_num_rows($result)) > 0)  //if the department already exists
      {
         mysqli_free_result($result);
         if ($link)
         {
            mysqli_close($link);
            $link = 0;
         }
         echo ERROR_SAME_NAME;
         return;
      }
      else
      {
         mysqli_free_result($result);
         if (!mysqli_query($link, $str_update))  //update failed
         {
            if ($link)
            {
               mysqli_close($link);
               $link = 0;
            }
            echo DB_ERROR;
            return;
         }
         if (!mysqli_query($link, $str_update_2))  //update failed
         {
            if ($link)
            {
               mysqli_close($link);
               $link = 0;
            }
            echo DB_ERROR;
            return;
         }
         else  //update successfully
         {            
            //3.refresh department page
            if(file_exists(REFRESH_PATH))
            {
               include_once(REFRESH_PATH);
            }
            else
            {
               sleep(DELAY_SEC);
               echo FILE_ERROR;
               return;
            }
            //$link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
            $refresh_str = refreshDepartPage($link, $GUID);

            //close link
			   if ($link)
            {
               mysqli_close($link);
               $link = 0;
            }
            echo $refresh_str;           
         }
      }
   }
   else
   {      
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      sleep(DELAY_SEC);
      echo DB_ERROR;
      return;
   }
   
?>
