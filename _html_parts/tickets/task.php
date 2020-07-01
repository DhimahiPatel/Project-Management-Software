<?php  
 $connect = mysqli_connect("localhost", "root", "", "user1");  
 $sql = "SELECT users.id,users.username ,
          GROUP_CONCAT(tickets.type) from users,tickets where users.id=tickets.assignee group by users.id";  
 $result = mysqli_query($connect, $sql);  
 ?>  
 <!DOCTYPE html>  
 <html>  
      <body>  
           <br />  
           <div class="container" style="width:500px;">  
                <div class="table-responsive">  
                     <table class="table table-striped">  
                          <tr>  
                               <th>ID</th>
                               <th>User Name</th>
                               <th>Type</th>
                          </tr>  
                          <?php  
                          if(mysqli_num_rows($result) > 0)  
                          {  
                               while($row = mysqli_fetch_array($result))  
                               {  
                          ?>  
                          <tr>  
                               <td><?php echo $row["id"];?></td> 
                               <td><?php echo $row["username"];?></td> 
                                <td><?php echo $row["GROUP_CONCAT(tickets.type)"];?></td>   
                          </tr>  
                          <?php  
                               }  
                          }  
                          ?>  
                     </table>  
                </div>  
           </div>  
           <br />  
      </body> 
  </html> 