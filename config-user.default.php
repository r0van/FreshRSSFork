<?php

# Do not modify this file, which defines default values, but create a
# `./data/config-user.custom.php` file instead, containing the keys you want to
# override.
return array (
	'enabled' => true,
	'is_admin' => false,
	'language' => 'en',
	// A timezone identifier such as 'Europe/Paris' https://php.net/timezones or blank for server default
	'timezone' => '',
	'archiving' => [
		'keep_period' => 'P3M',
		'keep_max' => 200,
		'keep_min' => 50,
		'keep_favourites' => true,
		'keep_labels' => true,
		'keep_unreads' => false,
	],
	'ttl_default' => 3600,
	'dynamic_opml_ttl_default' => 43200,
	'mail_login' => '',
	'email_validation_token' => '',
	'token' => '',
	'passwordHash' => '',
	'apiPasswordHash' => '',
	//feverKey is md5($user . ':' . $apiPasswordPlain)
	'feverKey' => '',
	'posts_per_page' => 20,
	'since_hours_posts_per_rss' => 168,
	'max_posts_per_rss' => 400,
	'view_mode' => 'normal',
	'default_view' => 'adaptive',
	'default_state' => FreshRSS_Entry::STATE_NOT_READ,
	'show_fav_unread' => false,
	'auto_load_more' => true,
	'display_posts' => false,
	'display_categories' => 'active',	//{ active, remember, all, none }
	'show_tags' => 'f',	// {0 => none, b => both, f => footer, h => header}
	'show_tags_max' => 7,
	'show_author_date' => 'h',	// {0 => none, b => both, f => footer, h => header}
	'show_feed_name' => 'a',	// {0 => none, a => with authors, t => above title}
	'show_article_icons' => 't', // {a => with_authors, t => above title}
	'hide_read_feeds' => true,
	'onread_jump_next' => true,
	'lazyload' => true,
	'sides_close_article' => false,
	'sticky_post' => true,
	'reading_confirm' => false,
	'auto_remove_article' => false,

	# In the case an article has changed (e.g. updated content):
	#	Set to `true` to mark it unread, or `false` to leave it as-is.
	'mark_updated_article_unread' => false, //TODO: -1 => ignore, 0 => update, 1 => update and mark as unread

	'sort' => 'id',
	'mark_read_button' => 'big',
	'sort_order' => 'DESC',
	'anon_access' => false,
	'mark_when' => array (
		'article' => true,
		'gone' => false,
		'max_n_unread' => false,
		'reception' => false,
		'same_title_in_feed' => false,
		'scroll' => false,
		'focus' => false,
		'site' => true,
	),
	'filters' => [],
	'theme' => 'Origine',
	'darkMode' => 'auto',
	'content_width' => 'thin',
	'shortcuts' => array (
		'actualize' => 'q',
		'mark_read' => 'r',
		'mark_favorite' => 'f',
		'go_website' => 'space',
		'next_entry' => 'j',
		'next_unread_entry' => 'h',
		'prev_entry' => 'k',
		'skip_next_entry' => 'n',
		'skip_prev_entry' => 'p',
		'first_entry' => 'home',
		'last_entry' => 'end',
		'collapse_entry' => 'c',
		'load_more' => 'm',
		'mylabels' => 'l',
		'auto_share' => 's',
		'focus_search' => 'a',
		'user_filter' => 'u',
		'help' => 'f1',
		'close_dropdown' => 'escape',
		'normal_view' => '1',
		'global_view' => '2',
		'reading_view' => '3',
		'toggle_media' => 'v',
	),

	# Disabling favicons and using emojis instead of icons improves performance for users with many feeds
	'show_favicons' => true,
	'icons_as_emojis' => false,
	# Hide the dropdown configuration menu and favicon in the aside list in case of many feeds, for UI performance
	'simplify_over_n_feeds' => 1000,

	'topline_read' => true,
	'topline_favorite' => true,
	'topline_myLabels' => false,
	'topline_sharing' => false,
	'topline_website' => 'full',
	'topline_thumbnail' => 'none',
	'topline_summary' => false,
	'topline_display_authors' => false,
	'topline_date' => true,
	'topline_link' => true,
	'bottomline_read' => true,
	'bottomline_favorite' => true,
	'bottomline_sharing' => true,
	'bottomline_tags' => true,
	'bottomline_myLabels' => true,
	'bottomline_date' => true,
	'bottomline_link' => true,
	'sharing' => array (
	),
	'queries' => array (
	),
	'html5_notif_timeout' => 0,
	'show_nav_buttons' => true,
	# List of enabled FreshRSS extensions.
	'extensions_enabled' => [],
	'retrieve_extension_list' => true,
	# Extensions configurations
	'extensions' => [],
);
