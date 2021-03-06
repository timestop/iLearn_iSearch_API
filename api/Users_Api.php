<?php
   if(is_array($_GET)&&count($_GET)>0){   //判断是否有Get参数
      if(isset($_GET["did"])){
         $uid = $_GET["uid"];
      }
      else {
         echo json_encode(array("status"=>-2, "result"=>"用户不存在！")); //-2没有传部门ID
         return;
      }
   }
   else {
      echo json_encode(array("status"=>-1, "result"=>"用户不存在！")); //-1没有传任何参数
      return;
   }
   define("FILE_NAME", "d:/phptest/DB.conf");
   define("DELAY_SEC", 3);
   define("FILE_ERROR", -2);
   
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
   
   header('Content-Type:application/json;charset=utf-8');
   
   //define
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
   define("TIME_ZONE", "Asia/Shanghai");
   define("ILLEGAL_CHAR", "'-;<>");                         //illegal char

   //return value
   define("SUCCESS", 0);
   define("DB_ERROR", -1);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);      

   //query
   $link;
   $str_query;
   $str_update;
   $result;                 //query result
   $DeptId;
   $UserName;
   $Email;
   $EmployeeId;
   
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }
   
   $data1 = array();
   $data2 = array();
   class Stunews{
      public $newtitle;
      public $newmsg;
      public $edittime;
      public $occurtime;
   }
   $str_user = "select UserName, Email, DeptId, EmployeeId from wutian.users where UserId = " . $uid;
   if($result = mysqli_query($link, $str_user)){
      $row_number = mysqli_num_rows($result);
      if ($row_number > 0)
      {
         $userrow = mysqli_fetch_assoc($result);
         $DeptId = $userrow["DeptId"];
         $UserName = $userrow["UserName"];
         $Email = $userrow["Email"];
         $EmployeeId = $userrow["EmployeeId"];
      }
      else {
         if($link){
            mysqli_close($link);
         }
         sleep(DELAY_SEC);
         // echo -__LINE__;
         echo json_encode(array("status"=>0, "result"=>"用户不存在！")); 
         return;
      }
   }
   mysqli_close($link);
   echo json_encode(array("status"=>1, "UserName"=>$UserName, "Email"=>$Email, "EmployeeId"=>$EmployeeId, "DeptId"=>$DeptId, "result"=>"用户获取成功！"));      
   return;
?>
