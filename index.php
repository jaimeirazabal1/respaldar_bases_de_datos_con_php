<?php

backup_tables('localhost','root','','u669571457_db','*',"db_backup/");


/* backup the db OR just a table */
//En la variable $talbes puedes agregar las tablas especificas separadas por comas:
//profesor,estudiante,clase
//O déjalo con el asterisco '*' para que se respalde toda la base de datos

function backup_tables($host,$user,$pass,$name,$tables = '*',$backup_folder = '')
{
   
   $link = mysql_connect($host,$user,$pass);
   mysql_select_db($name,$link);
   
   //get all of the tables
   if($tables == '*')
   {
      $tables = array();
      $result = mysql_query('SHOW TABLES');
      while($row = mysql_fetch_row($result))
      {
         $tables[] = $row[0];
      }
   }
   else
   {
      $tables = is_array($tables) ? $tables : explode(',',$tables);
   }
   $return = '';
   //cycle through
   foreach($tables as $table)
   {
      $result = mysql_query('SELECT * FROM '.$table);
      $num_fields = mysql_num_fields($result);
      
      $return.= 'DROP TABLE '.$table.';';
      $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
      $return.= "\n\n".$row2[1].";\n\n";
      
    for ($i = 0; $i < $num_fields; $i++)
      {
         while($row = mysql_fetch_row($result))
         {
            $return.= 'INSERT INTO '.$table.' VALUES(';
            for($j=0; $j<$num_fields; $j++) 
            {
               $row[$j] = addslashes($row[$j]);
               $row[$j] = ereg_replace("\n","\\n",$row[$j]);
               if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
               if ($j<($num_fields-1)) { $return.= ','; }
            }
            $return.= ");\n";
         }
      }
      $return.="\n\n\n";
   }
   
   //save file
   $new_file = $backup_folder.'db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql';
   $handle = fopen($new_file,'w+');
   if(fwrite($handle,$return)){
      $zip = new ZipArchive();

      $filename = "respaldo_".date('Y_m_d_H_i_s_').".zip";
       
      if($zip->open($filename,ZIPARCHIVE::CREATE)===true) {
              //$zip->addFile('a.txt');
              $zip->addFile($new_file);
              $zip->close();
              echo '<br>Creado '.$filename;
      }
      else {
              echo 'Error creando '.$filename;
      }
      echo "<a href='".$filename."'>$filename</a>";
   }
   fclose($handle);
}


?>