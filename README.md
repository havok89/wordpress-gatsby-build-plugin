# wordpress-gatsby-build-plugin
Plugin for WordPress to trigger a gatsby build using the GIT API

This is a quick and dirty plugin created for a site I was working on. May be useful for someone else.

## Purposes of the plugin:
- Used to trigger a build of gatsby
- Triggered manually from a page in the CMS
- Allows user to rebuild the site once they have updated content rather than on every page save


## My Scenario
I am running a WordPress CMS instance on a subdomain (cms.domain.com) and have built a Gatsby site which uses the WP-API to get site data.
I am then querying this data within Gatsby with GraphQL and the whole front end of the site is done there.
Since Gatsby creates a static site I need a solution to trigger a build of the site once the CMS content has changed (page edits, new pages etc)

I am using Github Actions to build my site along with an FTP action to publish the built site to my hosting space - see my fork of the FTP action to include deleting everything in the remote folder that doesnt exist in the upload if that may be helpful for you too. havok89/ftp-action


## Usage
To use this plugin you will need to create an access token for your github repo and update the settings in the CMS with this token and the repo name - the name should just be "username/reponame" e.g. havok89/wordpress-gatsby-build-plugin

Once the settings are entered the admin page will show a build button, clicking this will call the Github API and trigger a build if you have your action set up

See included example workflow file, it includes building, deploying via FTP (removing any files from remote that arent needed) and cleanup of artifacts
