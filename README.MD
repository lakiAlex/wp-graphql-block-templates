# WPGraphQL Block Templates

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0.html)

## Overview

This plugin provides WPGraphQL fields to access WordPress site-editor/block templates. It allows you to query block templates and retrieve their data in JSON format, making it easier to work with block data in decoupled WP applications.

## Installation

1. Download the plugin from the [GitHub repository](https://github.com/lakiAlex/wpgraphql-block-templates).
2. Upload the plugin files to the `/wp-content/plugins/wpgraphql-block-templates` directory, or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin through the 'Plugins' screen in WordPress.

## Requirements

-   WordPress 6.3 or greater
-   PHP 7.1 or greater
-   WPGraphQL plugin

## Usage

Once the plugin is activated, you can use the new WPGraphQL fields to query block templates. Here is an example query:

```graphql
{
	siteEditorTemplates {
		productCatalog
		searchResults
	}
}
```

All available templates that are within site editor will be available also within WPGraphQL query composer.

## License

This plugin is licensed under the GPL-3.0 License. See the [LICENSE](https://www.gnu.org/licenses/gpl-3.0.html) file for more information.
