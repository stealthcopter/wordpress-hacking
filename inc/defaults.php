<?php

$DEFAULT_ROUTES = [
    '/',
    '/batch/v1',
    '/oembed/1.0',
    '/oembed/1.0/embed',
    '/oembed/1.0/embed',
    '/oembed/1.0/proxy',
    '/oembed/1.0/proxy',
    '/wp/v2',
    '/wp/v2/posts',
    '/wp/v2/posts',
    '/wp/v2/posts',
    '/wp/v2/posts',
    '/wp/v2/posts/(?P<id>[\d]+)',
    '/wp/v2/posts/(?P<id>[\d]+)',
    '/wp/v2/posts/(?P<id>[\d]+)',
    '/wp/v2/posts/(?P<id>[\d]+)',
    '/wp/v2/posts/(?P<id>[\d]+)',
    '/wp/v2/posts/(?P<id>[\d]+)',
    '/wp/v2/posts/(?P<parent>[\d]+)/revisions',
    '/wp/v2/posts/(?P<parent>[\d]+)/revisions',
    '/wp/v2/posts/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/posts/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/posts/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/posts/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/posts/(?P<id>[\d]+)/autosaves',
    '/wp/v2/posts/(?P<id>[\d]+)/autosaves',
    '/wp/v2/posts/(?P<id>[\d]+)/autosaves',
    '/wp/v2/posts/(?P<id>[\d]+)/autosaves',
    '/wp/v2/posts/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/posts/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/pages',
    '/wp/v2/pages',
    '/wp/v2/pages',
    '/wp/v2/pages',
    '/wp/v2/pages/(?P<id>[\d]+)',
    '/wp/v2/pages/(?P<id>[\d]+)',
    '/wp/v2/pages/(?P<id>[\d]+)',
    '/wp/v2/pages/(?P<id>[\d]+)',
    '/wp/v2/pages/(?P<id>[\d]+)',
    '/wp/v2/pages/(?P<id>[\d]+)',
    '/wp/v2/pages/(?P<parent>[\d]+)/revisions',
    '/wp/v2/pages/(?P<parent>[\d]+)/revisions',
    '/wp/v2/pages/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/pages/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/pages/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/pages/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/pages/(?P<id>[\d]+)/autosaves',
    '/wp/v2/pages/(?P<id>[\d]+)/autosaves',
    '/wp/v2/pages/(?P<id>[\d]+)/autosaves',
    '/wp/v2/pages/(?P<id>[\d]+)/autosaves',
    '/wp/v2/pages/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/pages/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/media',
    '/wp/v2/media',
    '/wp/v2/media',
    '/wp/v2/media',
    '/wp/v2/media/(?P<id>[\d]+)',
    '/wp/v2/media/(?P<id>[\d]+)',
    '/wp/v2/media/(?P<id>[\d]+)',
    '/wp/v2/media/(?P<id>[\d]+)',
    '/wp/v2/media/(?P<id>[\d]+)',
    '/wp/v2/media/(?P<id>[\d]+)',
    '/wp/v2/media/(?P<id>[\d]+)/post-process',
    '/wp/v2/media/(?P<id>[\d]+)/post-process',
    '/wp/v2/media/(?P<id>[\d]+)/edit',
    '/wp/v2/media/(?P<id>[\d]+)/edit',
    '/wp/v2/menu-items',
    '/wp/v2/menu-items',
    '/wp/v2/menu-items',
    '/wp/v2/menu-items',
    '/wp/v2/menu-items/(?P<id>[\d]+)',
    '/wp/v2/menu-items/(?P<id>[\d]+)',
    '/wp/v2/menu-items/(?P<id>[\d]+)',
    '/wp/v2/menu-items/(?P<id>[\d]+)',
    '/wp/v2/menu-items/(?P<id>[\d]+)',
    '/wp/v2/menu-items/(?P<id>[\d]+)',
    '/wp/v2/menu-items/(?P<id>[\d]+)/autosaves',
    '/wp/v2/menu-items/(?P<id>[\d]+)/autosaves',
    '/wp/v2/menu-items/(?P<id>[\d]+)/autosaves',
    '/wp/v2/menu-items/(?P<id>[\d]+)/autosaves',
    '/wp/v2/menu-items/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/menu-items/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/blocks',
    '/wp/v2/blocks',
    '/wp/v2/blocks',
    '/wp/v2/blocks',
    '/wp/v2/blocks/(?P<id>[\d]+)',
    '/wp/v2/blocks/(?P<id>[\d]+)',
    '/wp/v2/blocks/(?P<id>[\d]+)',
    '/wp/v2/blocks/(?P<id>[\d]+)',
    '/wp/v2/blocks/(?P<id>[\d]+)',
    '/wp/v2/blocks/(?P<id>[\d]+)',
    '/wp/v2/blocks/(?P<parent>[\d]+)/revisions',
    '/wp/v2/blocks/(?P<parent>[\d]+)/revisions',
    '/wp/v2/blocks/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/blocks/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/blocks/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/blocks/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/blocks/(?P<id>[\d]+)/autosaves',
    '/wp/v2/blocks/(?P<id>[\d]+)/autosaves',
    '/wp/v2/blocks/(?P<id>[\d]+)/autosaves',
    '/wp/v2/blocks/(?P<id>[\d]+)/autosaves',
    '/wp/v2/blocks/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/blocks/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/templates/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/revisions',
    '/wp/v2/templates/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/revisions',
    '/wp/v2/templates/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/templates/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/templates/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/templates/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/templates/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/autosaves',
    '/wp/v2/templates/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/autosaves',
    '/wp/v2/templates/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/autosaves',
    '/wp/v2/templates/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/autosaves',
    '/wp/v2/templates/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/templates/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/templates',
    '/wp/v2/templates',
    '/wp/v2/templates',
    '/wp/v2/templates',
    '/wp/v2/templates/lookup',
    '/wp/v2/templates/lookup',
    '/wp/v2/templates/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)',
    '/wp/v2/templates/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)',
    '/wp/v2/templates/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)',
    '/wp/v2/templates/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)',
    '/wp/v2/templates/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)',
    '/wp/v2/templates/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)',
    '/wp/v2/template-parts/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/revisions',
    '/wp/v2/template-parts/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/revisions',
    '/wp/v2/template-parts/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/template-parts/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/template-parts/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/template-parts/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/template-parts/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/autosaves',
    '/wp/v2/template-parts/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/autosaves',
    '/wp/v2/template-parts/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/autosaves',
    '/wp/v2/template-parts/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/autosaves',
    '/wp/v2/template-parts/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/template-parts/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/template-parts',
    '/wp/v2/template-parts',
    '/wp/v2/template-parts',
    '/wp/v2/template-parts',
    '/wp/v2/template-parts/lookup',
    '/wp/v2/template-parts/lookup',
    '/wp/v2/template-parts/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)',
    '/wp/v2/template-parts/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)',
    '/wp/v2/template-parts/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)',
    '/wp/v2/template-parts/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)',
    '/wp/v2/template-parts/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)',
    '/wp/v2/template-parts/(?P<id>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)',
    '/wp/v2/global-styles/(?P<parent>[\d]+)/revisions',
    '/wp/v2/global-styles/(?P<parent>[\d]+)/revisions',
    '/wp/v2/global-styles/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/global-styles/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/global-styles/themes/(?P<stylesheet>[\/\s%\w\.\(\)\[\]\@_\-]+)/variations',
    '/wp/v2/global-styles/themes/(?P<stylesheet>[\/\s%\w\.\(\)\[\]\@_\-]+)/variations',
    '/wp/v2/global-styles/themes/(?P<stylesheet>[^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)',
    '/wp/v2/global-styles/themes/(?P<stylesheet>[^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)',
    '/wp/v2/global-styles/(?P<id>[\/\w-]+)',
    '/wp/v2/global-styles/(?P<id>[\/\w-]+)',
    '/wp/v2/global-styles/(?P<id>[\/\w-]+)',
    '/wp/v2/global-styles/(?P<id>[\/\w-]+)',
    '/wp/v2/navigation',
    '/wp/v2/navigation',
    '/wp/v2/navigation',
    '/wp/v2/navigation',
    '/wp/v2/navigation/(?P<id>[\d]+)',
    '/wp/v2/navigation/(?P<id>[\d]+)',
    '/wp/v2/navigation/(?P<id>[\d]+)',
    '/wp/v2/navigation/(?P<id>[\d]+)',
    '/wp/v2/navigation/(?P<id>[\d]+)',
    '/wp/v2/navigation/(?P<id>[\d]+)',
    '/wp/v2/navigation/(?P<parent>[\d]+)/revisions',
    '/wp/v2/navigation/(?P<parent>[\d]+)/revisions',
    '/wp/v2/navigation/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/navigation/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/navigation/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/navigation/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)',
    '/wp/v2/navigation/(?P<id>[\d]+)/autosaves',
    '/wp/v2/navigation/(?P<id>[\d]+)/autosaves',
    '/wp/v2/navigation/(?P<id>[\d]+)/autosaves',
    '/wp/v2/navigation/(?P<id>[\d]+)/autosaves',
    '/wp/v2/navigation/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/navigation/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)',
    '/wp/v2/font-families',
    '/wp/v2/font-families',
    '/wp/v2/font-families',
    '/wp/v2/font-families',
    '/wp/v2/font-families/(?P<id>[\d]+)',
    '/wp/v2/font-families/(?P<id>[\d]+)',
    '/wp/v2/font-families/(?P<id>[\d]+)',
    '/wp/v2/font-families/(?P<id>[\d]+)',
    '/wp/v2/font-families/(?P<id>[\d]+)',
    '/wp/v2/font-families/(?P<id>[\d]+)',
    '/wp/v2/font-families/(?P<font_family_id>[\d]+)/font-faces',
    '/wp/v2/font-families/(?P<font_family_id>[\d]+)/font-faces',
    '/wp/v2/font-families/(?P<font_family_id>[\d]+)/font-faces',
    '/wp/v2/font-families/(?P<font_family_id>[\d]+)/font-faces',
    '/wp/v2/font-families/(?P<font_family_id>[\d]+)/font-faces/(?P<id>[\d]+)',
    '/wp/v2/font-families/(?P<font_family_id>[\d]+)/font-faces/(?P<id>[\d]+)',
    '/wp/v2/font-families/(?P<font_family_id>[\d]+)/font-faces/(?P<id>[\d]+)',
    '/wp/v2/font-families/(?P<font_family_id>[\d]+)/font-faces/(?P<id>[\d]+)',
    '/wp/v2/types',
    '/wp/v2/types',
    '/wp/v2/types/(?P<type>[\w-]+)',
    '/wp/v2/types/(?P<type>[\w-]+)',
    '/wp/v2/statuses',
    '/wp/v2/statuses',
    '/wp/v2/statuses/(?P<status>[\w-]+)',
    '/wp/v2/statuses/(?P<status>[\w-]+)',
    '/wp/v2/taxonomies',
    '/wp/v2/taxonomies',
    '/wp/v2/taxonomies/(?P<taxonomy>[\w-]+)',
    '/wp/v2/taxonomies/(?P<taxonomy>[\w-]+)',
    '/wp/v2/categories',
    '/wp/v2/categories',
    '/wp/v2/categories',
    '/wp/v2/categories',
    '/wp/v2/categories/(?P<id>[\d]+)',
    '/wp/v2/categories/(?P<id>[\d]+)',
    '/wp/v2/categories/(?P<id>[\d]+)',
    '/wp/v2/categories/(?P<id>[\d]+)',
    '/wp/v2/categories/(?P<id>[\d]+)',
    '/wp/v2/categories/(?P<id>[\d]+)',
    '/wp/v2/tags',
    '/wp/v2/tags',
    '/wp/v2/tags',
    '/wp/v2/tags',
    '/wp/v2/tags/(?P<id>[\d]+)',
    '/wp/v2/tags/(?P<id>[\d]+)',
    '/wp/v2/tags/(?P<id>[\d]+)',
    '/wp/v2/tags/(?P<id>[\d]+)',
    '/wp/v2/tags/(?P<id>[\d]+)',
    '/wp/v2/tags/(?P<id>[\d]+)',
    '/wp/v2/menus',
    '/wp/v2/menus',
    '/wp/v2/menus',
    '/wp/v2/menus',
    '/wp/v2/menus/(?P<id>[\d]+)',
    '/wp/v2/menus/(?P<id>[\d]+)',
    '/wp/v2/menus/(?P<id>[\d]+)',
    '/wp/v2/menus/(?P<id>[\d]+)',
    '/wp/v2/menus/(?P<id>[\d]+)',
    '/wp/v2/menus/(?P<id>[\d]+)',
    '/wp/v2/wp_pattern_category',
    '/wp/v2/wp_pattern_category',
    '/wp/v2/wp_pattern_category',
    '/wp/v2/wp_pattern_category',
    '/wp/v2/wp_pattern_category/(?P<id>[\d]+)',
    '/wp/v2/wp_pattern_category/(?P<id>[\d]+)',
    '/wp/v2/wp_pattern_category/(?P<id>[\d]+)',
    '/wp/v2/wp_pattern_category/(?P<id>[\d]+)',
    '/wp/v2/wp_pattern_category/(?P<id>[\d]+)',
    '/wp/v2/wp_pattern_category/(?P<id>[\d]+)',
    '/wp/v2/users',
    '/wp/v2/users',
    '/wp/v2/users',
    '/wp/v2/users',
    '/wp/v2/users/(?P<id>[\d]+)',
    '/wp/v2/users/(?P<id>[\d]+)',
    '/wp/v2/users/(?P<id>[\d]+)',
    '/wp/v2/users/(?P<id>[\d]+)',
    '/wp/v2/users/(?P<id>[\d]+)',
    '/wp/v2/users/(?P<id>[\d]+)',
    '/wp/v2/users/me',
    '/wp/v2/users/me',
    '/wp/v2/users/me',
    '/wp/v2/users/me',
    '/wp/v2/users/me',
    '/wp/v2/users/me',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/introspect',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/introspect',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/(?P<uuid>[\w\-]+)',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/(?P<uuid>[\w\-]+)',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/(?P<uuid>[\w\-]+)',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/(?P<uuid>[\w\-]+)',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/(?P<uuid>[\w\-]+)',
    '/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/(?P<uuid>[\w\-]+)',
    '/wp/v2/comments',
    '/wp/v2/comments',
    '/wp/v2/comments',
    '/wp/v2/comments',
    '/wp/v2/comments/(?P<id>[\d]+)',
    '/wp/v2/comments/(?P<id>[\d]+)',
    '/wp/v2/comments/(?P<id>[\d]+)',
    '/wp/v2/comments/(?P<id>[\d]+)',
    '/wp/v2/comments/(?P<id>[\d]+)',
    '/wp/v2/comments/(?P<id>[\d]+)',
    '/wp/v2/search',
    '/wp/v2/search',
    '/wp/v2/block-renderer/(?P<name>[a-z0-9-]+/[a-z0-9-]+)',
    '/wp/v2/block-renderer/(?P<name>[a-z0-9-]+/[a-z0-9-]+)',
    '/wp/v2/block-types',
    '/wp/v2/block-types',
    '/wp/v2/block-types/(?P<namespace>[a-zA-Z0-9_-]+)',
    '/wp/v2/block-types/(?P<namespace>[a-zA-Z0-9_-]+)',
    '/wp/v2/block-types/(?P<namespace>[a-zA-Z0-9_-]+)/(?P<name>[a-zA-Z0-9_-]+)',
    '/wp/v2/block-types/(?P<namespace>[a-zA-Z0-9_-]+)/(?P<name>[a-zA-Z0-9_-]+)',
    '/wp/v2/settings',
    '/wp/v2/settings',
    '/wp/v2/settings',
    '/wp/v2/settings',
    '/wp/v2/themes',
    '/wp/v2/themes',
    '/wp/v2/themes/(?P<stylesheet>[^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)',
    '/wp/v2/themes/(?P<stylesheet>[^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)',
    '/wp/v2/plugins',
    '/wp/v2/plugins',
    '/wp/v2/plugins',
    '/wp/v2/plugins',
    '/wp/v2/plugins/(?P<plugin>[^.\/]+(?:\/[^.\/]+)?)',
    '/wp/v2/plugins/(?P<plugin>[^.\/]+(?:\/[^.\/]+)?)',
    '/wp/v2/plugins/(?P<plugin>[^.\/]+(?:\/[^.\/]+)?)',
    '/wp/v2/plugins/(?P<plugin>[^.\/]+(?:\/[^.\/]+)?)',
    '/wp/v2/plugins/(?P<plugin>[^.\/]+(?:\/[^.\/]+)?)',
    '/wp/v2/plugins/(?P<plugin>[^.\/]+(?:\/[^.\/]+)?)',
    '/wp/v2/sidebars',
    '/wp/v2/sidebars',
    '/wp/v2/sidebars/(?P<id>[\w-]+)',
    '/wp/v2/sidebars/(?P<id>[\w-]+)',
    '/wp/v2/sidebars/(?P<id>[\w-]+)',
    '/wp/v2/sidebars/(?P<id>[\w-]+)',
    '/wp/v2/widget-types',
    '/wp/v2/widget-types',
    '/wp/v2/widget-types/(?P<id>[a-zA-Z0-9_-]+)',
    '/wp/v2/widget-types/(?P<id>[a-zA-Z0-9_-]+)',
    '/wp/v2/widget-types/(?P<id>[a-zA-Z0-9_-]+)/encode',
    '/wp/v2/widget-types/(?P<id>[a-zA-Z0-9_-]+)/encode',
    '/wp/v2/widget-types/(?P<id>[a-zA-Z0-9_-]+)/render',
    '/wp/v2/widget-types/(?P<id>[a-zA-Z0-9_-]+)/render',
    '/wp/v2/widgets',
    '/wp/v2/widgets',
    '/wp/v2/widgets',
    '/wp/v2/widgets',
    '/wp/v2/widgets/(?P<id>[\w\-]+)',
    '/wp/v2/widgets/(?P<id>[\w\-]+)',
    '/wp/v2/widgets/(?P<id>[\w\-]+)',
    '/wp/v2/widgets/(?P<id>[\w\-]+)',
    '/wp/v2/widgets/(?P<id>[\w\-]+)',
    '/wp/v2/widgets/(?P<id>[\w\-]+)',
    '/wp/v2/block-directory/search',
    '/wp/v2/block-directory/search',
    '/wp/v2/pattern-directory/patterns',
    '/wp/v2/pattern-directory/patterns',
    '/wp/v2/block-patterns/patterns',
    '/wp/v2/block-patterns/patterns',
    '/wp/v2/block-patterns/categories',
    '/wp/v2/block-patterns/categories',
    '/wp-site-health/v1',
    '/wp-site-health/v1/tests/background-updates',
    '/wp-site-health/v1/tests/background-updates',
    '/wp-site-health/v1/tests/loopback-requests',
    '/wp-site-health/v1/tests/loopback-requests',
    '/wp-site-health/v1/tests/https-status',
    '/wp-site-health/v1/tests/https-status',
    '/wp-site-health/v1/tests/dotorg-communication',
    '/wp-site-health/v1/tests/dotorg-communication',
    '/wp-site-health/v1/tests/authorization-header',
    '/wp-site-health/v1/tests/authorization-header',
    '/wp-site-health/v1/directory-sizes',
    '/wp-site-health/v1/directory-sizes',
    '/wp-site-health/v1/tests/page-cache',
    '/wp-site-health/v1/tests/page-cache',
    '/wp-block-editor/v1',
    '/wp-block-editor/v1/url-details',
    '/wp-block-editor/v1/url-details',
    '/wp/v2/menu-locations',
    '/wp/v2/menu-locations',
    '/wp/v2/menu-locations/(?P<location>[\w-]+)',
    '/wp/v2/menu-locations/(?P<location>[\w-]+)',
    '/wp-block-editor/v1/export',
    '/wp-block-editor/v1/export',
    '/wp-block-editor/v1/navigation-fallback',
    '/wp-block-editor/v1/navigation-fallback',
    '/wp/v2/font-collections',
    '/wp/v2/font-collections',
    '/wp/v2/font-collections/(?P<slug>[\/\w-]+)',
    '/wp/v2/font-collections/(?P<slug>[\/\w-]+)',
];