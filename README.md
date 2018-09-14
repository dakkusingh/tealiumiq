# Tealium iQ Tag Management
[![CircleCI](https://circleci.com/gh/dakkusingh/tealiumiq.svg?style=svg)](https://circleci.com/gh/dakkusingh/tealiumiq)
The Tealium iQ tag management system puts you in control of 
your marketing technology implementations making it easy to 
deploy new vendor tags and make edits to existing ones in our 
user friendly console.

This module provides Drupal 8 integration with Tealium iQ.

![tealium_iq](https://www.drupal.org/files/what_are_tags_01.png)

## Requirements
Tealium iQ module for Drupal 8 requires the following:

- Token Module - Provides a popup browser to see the available tokens
 for use in Tealium iQ tag fields.

## Features
The primary features include:

- An administration interface to manage default Tealium tags. - TODO
- Use of standard fields for entity support, allowing for translation
 and revisioning of Tealium tag values added for individual entities.
- Basic of Tealium tags available, covering commonly used tags
- A plugin interface allowing for additional Tealium tags to be easily
 added via custom modules.
 - Tokens may be used to automatically assign values.
 
## Usage scenario - Tealium Tags Field 
### Install the module.
- Open admin/config/services/tealiumiq.
- Add the Tealium iQ account details. 

### Add Tealium Tags Field
- To adjust Tealium Tags for a specific entity, the Tealium field must
 be added first. Follow these steps:
- Go to the "Manage fields" of the bundle where the Tealium field is
 to appear.
- Select "Tealium tags" from the "Add a new field" selector.
- Fill in a label for the field, e.g. "Tealium tags", and set an
 appropriate machine name, e.g. "tealium_tags".
- Click the "Save and continue" button.
- If the site supports multiple languages, and translations have been
 enabled for this entity, select "Users may translate this field" to
  use Drupal's translation system.
  
## Usage Schenario - As API
todo - write documentation

## Usage Schenario - As Context Reaction
todo - write documentation

## Asynchronous and Synchronous Loading
This setting can be changed at:
`admin/config/services/tealiumiq`

### Asynchronous Loading
With asynchronous tracking, the browser can load the different 
tags in parallel. It no longer has to wait for a certain tag to 
load completely before moving on to the next or the rest of the page 
content.

![tealium_async](https://tealium.com/wp-content/uploads/2015/03/oct-11-asynchronous-tags.gif)

### Synchronous Loading
When a page loads a synchronous tag, it waits for the tag content to 
load before moving on to the next content. The figure below shows an 
example of a page loading 4 tags in a synchronous or serial manner. 
The page starts by loading the first tag. After the tag has been 
completely loaded, the page moves on the second tag.

![tealium_iq](https://tealium.com/wp-content/uploads/2015/03/oct-11-synchronous-tags.gif)

### Official documentation: 
https://tealium.com/blog/standard/asynchronous-tagging/
