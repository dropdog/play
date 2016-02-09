### Drupal 8.x local development settings

## Usage
```
// On a fresh Drupal installation
// go to the drupal root folder
cd [drupal-root]

// Clone this repo
git clone git@github.com:dropdog/local-dev.git --branch live

// Allow the settings.php file to be editable
chmod 777 /path/to/settings.php

// Append the settings.local.php file to the site settings.php
// cat [drupal-root]/local-dev/add-to-settings.txt > /path/to/settings.php
cat local-dev/add-to-settings.txt >> sites/default/settings.php

// Reset the settings.php file permissions
chmod 444 /path/to/settings.php

// Clear Drupal caches
drush cr

// Ready! Now your site will be on full development mode!
```

## Patches

If there are any errors with the development services yml file such as **You have requested a non-existent service "cache.backend.null"**, please apply the patch from https://www.drupal.org/node/2348219#comment-10711754.

