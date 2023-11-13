![PHP Lint](https://github.com/FATCHIP-GmbH/project-template/workflows/PHP%20Lint/badge.svg)
# FATCHIP project-template
Template for creating a new Github project repository in FATCHIP style


### _FCCONFIG
Here we store all files that differ for each environment in a subfolder like `live` or `stage`.\
DeployHQ will automatically deploy the right folderes to the right environment if configured properly.
### _FCDEPLOY
Here all scripts regarding the [DeployHQ](https://fatchip-gmbh.deployhq.com/) Deployment process are located.
### _FCPROJECT
SQL files, docs, external configs...
### _RUNDECK
Folder for Rundecks GIT synch feature
### htdocs
This is where your project files go.
### .deployignore
These files will NOT be deployed by DeployHQ. [Syntax explanation](https://www.deployhq.com/support/excluded-files)
