## Installation

### Development mode

```bash
// Clone current repository as <my/www/folder> (website folder)
git clone git@github.com:dropdog/full-drupal.git <my/www/folder>

// Get into the folder
cd <my/www/folder>

// Switch to develop branch
git checkout develop

// Install Drupal with Drush or through the UI
drush site-install dropdog --db-url="mysql://<db_user>:<db_pass>@localhost/<db_name>"

// Enable common development modules (which are not enabled by default)
drush en -y devel devel_generate kint webprofiler dblog

// In order to complete the development mode run this script (may need sudo)
// This will add custom development settings as also as a custom config folder
sudo bash local-dev/local_dev.sh
```

### Live mode

```bash
// Just edit the settings.php file and change the $mode variable to 'live'
```
