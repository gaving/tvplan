# tvplan


                                       88                           
      ,d                               88                           
      88                               88                           
    MM88MMM  8b       d8  8b,dPPYba,   88  ,adPPYYba,  8b,dPPYba,   
      88     `8b     d8'  88P'    "8a  88  ""     `Y8  88P'   `"8a  
      88      `8b   d8'   88       d8  88  ,adPPPPP88  88       88  
      88,      `8b,d8'    88b,   ,a8"  88  88,    ,88  88       88  
      "Y888      "8"      88`YbbdP"'   88  `"8bbdP"Y8  88       88  
                          88                                        
                          88                                        

tvplan2 (http://gav.brokentrain.net/projects/tvplan)
online television show management and tracking

## Install

* Create suitable database, import tables into mysql with '\. lib/table_setup.sql' or use the shell script in 'lib/nuke.sh'.
* Copy and customise 'lib/templates/server_config.template' to 'lib/server_config.php' accordingly.
* Login as admin, change default admin password in config. 
* Administer more users through admin menu.
* Profit.

## Requirements

* MYSQL4.1/5, PHP5
* The directories cfg, profiles, tmp and templates_c to be writable by the webserver.

## screenshot

![interface](http://github.com/gaving/tvplan/raw/master/site/1.png)
![interface](http://github.com/gaving/tvplan/raw/master/site/2.png)
