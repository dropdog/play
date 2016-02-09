# !/bin/bash

sudo chmod 777 ../sites/default/settings.php

cat add-to-settings.txt >> ../sites/default/settings.php

sudo chmod 444 ../sites/default/settings.php

drush cr

