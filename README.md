## Overview

The module provides directory functionality for the SilverStripe CMS.

## Requirements

 * SilverStripe 3.1+

## Installation

Clone/download the git repository into a subfolder named "directory" in your SilverStripe project.
Add the following repository to your root composer.json:
git@gitlab.com:toddhossack/silverstripe-directory.git

## Usage

Create a directory using the directory manager (main menu). Add categories, nesting where required.
Add entries, and link them to one or more categories.
Next, create a directory page in the site tree, and link it to a directory. 
Override the templates as needed by copying and customising in your theme.
Extend the basic search form by creating an extension to the directory controller and using the available hooks.

## To do
 * Pagination for search results.
 * Nesting display for the categories grid field.
 * Browse categories functionality. 
 * Add to Packagist

## Contributing

### Translations

The code uses the translate function wherever possible. The base English language file has been provided.