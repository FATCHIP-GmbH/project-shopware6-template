# FATCHIP project-shopware6-template
Template for creating a new Github project repository in FATCHIP style


### _FCCONFIG
Here we store all files that differ for each environment in a subfolder like `prod` or `stage`.\
DeployHQ will automatically deploy the right folderes to the right environment if configured properly.
### _FCDEPLOY
Here all scripts regarding the [DeployHQ](https://fatchip-gmbh.deployhq.com/) Deployment process are located.
### htdocs
This is where your project files go.

Unter Windows beachten, um Git checkout zu machen:\
In cmd als Administrator ausfürhen:\
`git config --system core.longpaths true`\
siehe: https://stackoverflow.com/questions/22575662/filename-too-long-in-git-for-windows

### folgende Symlinks werden nach dem Deployment gesetzt:
- htdocs/public/media => $environment/shared/shopware/public/media
- htdocs/files/media => $environment/shared/shopware/files/media

### folgende Symlinks sind innerhalb des git repos:
- vendor/moorl/foundation => htdocs/custom/Plugins/MoorlFoundation
- shopware => htdocs

## Hosting
Bei iWelt. Stage im Unterordner von Live per Symlink.

## Updates
Nach Shopware/Plugin Updates die Datenabnkmigration ausführen durch:
php8.2 bin/console database:migrate --all