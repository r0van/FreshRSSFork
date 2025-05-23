<?php

/******************************************************************************/
/* Each entry of that file can be associated with a comment to indicate its   */
/* state. When there is no comment, it means the entry is fully translated.   */
/* The recognized comments are (comment matching is case-insensitive):        */
/*   + TODO: the entry has never been translated.                             */
/*   + DIRTY: the entry has been translated but needs to be updated.          */
/*   + IGNORE: the entry does not need to be translated.                      */
/* When a comment is not recognized, it is discarded.                         */
/******************************************************************************/

return array(
	'auth' => array(
		'allow_anonymous' => 'Autoriser la lecture anonyme des articles de l’utilisateur par défaut (%s)',
		'allow_anonymous_refresh' => 'Autoriser le rafraîchissement anonyme des flux',
		'api_enabled' => 'Autoriser l’accès par <abbr>API</abbr> <small>(nécessaire pour les applications mobiles et pour partager les filtres utilisateurs)</small>',
		'form' => 'Formulaire (traditionnel, requiert JavaScript)',
		'http' => 'HTTP (avancé : contrôlé par serveur Web, OIDC, SSO…)',
		'none' => 'Aucune (dangereux)',
		'title' => 'Authentification',
		'token' => 'Jeton d’identification maître',
		'token_help' => 'Permet d’accéder à toutes les sorties RSS de l’utilisateur et au rafraîchissement des flux sans besoin de s’authentifier :',
		'type' => 'Méthode d’authentification',
		'unsafe_autologin' => 'Autoriser les connexions automatiques non-sûres au format : ',
	),
	'check_install' => array(
		'cache' => array(
			'nok' => 'Veuillez vérifier les droits sur le répertoire <em>./data/cache</em>. Le serveur HTTP doit être capable d’écrire dedans',
			'ok' => 'Les droits sur le répertoire de cache sont bons.',
		),
		'categories' => array(
			'nok' => 'La table category est mal configurée.',
			'ok' => 'La table category est bien configurée.',
		),
		'connection' => array(
			'nok' => 'La connexion à la base de données est impossible.',
			'ok' => 'La connexion à la base de données est bonne.',
		),
		'ctype' => array(
			'nok' => 'Impossible de trouver une librairie pour la vérification des types de caractères (php-ctype).',
			'ok' => 'Vous disposez de la librairie pour la vérification des types de caractères (ctype).',
		),
		'curl' => array(
			'nok' => 'Impossible de trouver la librairie cURL (paquet php-curl).',
			'ok' => 'Vous disposez de la librairie cURL.',
		),
		'data' => array(
			'nok' => 'Veuillez vérifier les droits sur le répertoire <em>./data</em>. Le serveur HTTP doit être capable d’écrire dedans',
			'ok' => 'Les droits sur le répertoire de data sont bons.',
		),
		'database' => 'Installation de la base de données',
		'dom' => array(
			'nok' => 'Impossible de trouver une librairie pour parcourir le DOM (paquet php-xml).',
			'ok' => 'Vous disposez de la librairie pour parcourir le DOM.',
		),
		'entries' => array(
			'nok' => 'La table entry est mal configurée.',
			'ok' => 'La table entry est bien configurée.',
		),
		'favicons' => array(
			'nok' => 'Veuillez vérifier les droits sur le répertoire <em>./data/favicons</em>. Le serveur HTTP doit être capable d’écrire dedans',
			'ok' => 'Les droits sur le répertoire des favicons sont bons.',
		),
		'feeds' => array(
			'nok' => 'La table feed est mal configurée.',
			'ok' => 'La table feed est bien configurée.',
		),
		'fileinfo' => array(
			'nok' => 'Impossible de trouver la librairie PHP fileinfo (paquet fileinfo).',
			'ok' => 'Vous disposez de la librairie fileinfo.',
		),
		'files' => 'Installation des fichiers',
		'json' => array(
			'nok' => 'Vous ne disposez pas de l’extension recommandée JSON (paquet php-json).',
			'ok' => 'Vous disposez de l’extension recommandée JSON.',
		),
		'mbstring' => array(
			'nok' => 'Impossible de trouver la librairie recommandée mbstring pour Unicode.',
			'ok' => 'Vouz disposez de la librairie recommandée mbstring pour Unicode.',
		),
		'pcre' => array(
			'nok' => 'Impossible de trouver une librairie pour les expressions régulières (php-pcre).',
			'ok' => 'Vous disposez de la librairie pour les expressions régulières (PCRE).',
		),
		'pdo' => array(
			'nok' => 'Vous ne disposez pas de PDO ou d’un des drivers supportés (pdo_mysql, pdo_sqlite, pdo_pgsql).',
			'ok' => 'Vous disposez de PDO et d’au moins un des drivers supportés (pdo_mysql, pdo_sqlite, pdo_pgsql).',
		),
		'php' => array(
			'_' => 'Installation de PHP',
			'nok' => 'Votre version de PHP est la %s mais FreshRSS requiert au moins la version %s.',
			'ok' => 'Votre version de PHP est la %s, qui est compatible avec FreshRSS.',
		),
		'tables' => array(
			'nok' => 'Impossible de trouver une ou plusieurs tables en base de données.',
			'ok' => 'Les tables sont bien présentes en base de données.',
		),
		'title' => 'Vérification de l’installation',
		'tokens' => array(
			'nok' => 'Veuillez vérifier les droits sur le répertoire <em>./data/tokens</em>. Le serveur HTTP doit être capable d’écrire dedans',
			'ok' => 'Les droits sur le répertoire des tokens sont bons.',
		),
		'users' => array(
			'nok' => 'Veuillez vérifier les droits sur le répertoire <em>./data/users</em>. Le serveur HTTP doit être capable d’écrire dedans',
			'ok' => 'Les droits sur le répertoire des utilisateurs sont bons.',
		),
		'zip' => array(
			'nok' => 'Vous ne disposez pas de l’extension ZIP (paquet php-zip).',
			'ok' => 'Vous disposez de l’extension ZIP.',
		),
	),
	'extensions' => array(
		'author' => 'Auteur',
		'community' => 'Extensions utilisateur disponibles',
		'description' => 'Description',	// IGNORE
		'disabled' => 'Désactivée',
		'empty_list' => 'Aucune extension installée',
		'empty_list_help' => 'Vérifiez les logs pour déterminer pourquoi la liste des extensions est vide.',
		'enabled' => 'Activée',
		'latest' => 'Installée',
		'name' => 'Nom',
		'no_configure_view' => 'Cette extension n’a pas à être configurée',
		'system' => array(
			'_' => 'Extensions système',
			'no_rights' => 'Extensions système (contrôlées par l’administrateur)',
		),
		'title' => 'Extensions',	// IGNORE
		'update' => 'Mise à jour disponible',
		'user' => 'Extensions utilisateur',
		'version' => 'Version',	// IGNORE
	),
	'stats' => array(
		'_' => 'Statistiques',
		'all_feeds' => 'Tous les flux',
		'category' => 'Catégorie',
		'entry_count' => 'Nombre d’articles',
		'entry_per_category' => 'Articles par catégorie',
		'entry_per_day' => 'Nombre d’articles par jour (30 derniers jours)',
		'entry_per_day_of_week' => 'Par jour de la semaine (moyenne : %.2f messages)',
		'entry_per_hour' => 'Par heure (moyenne : %.2f messages)',
		'entry_per_month' => 'Par mois (moyenne : %.2f messages)',
		'entry_repartition' => 'Répartition des articles',
		'feed' => 'Flux',
		'feed_per_category' => 'Flux par catégorie',
		'idle' => 'Flux inactifs',
		'main' => 'Statistiques principales',
		'main_stream' => 'Flux principal',
		'no_idle' => 'Il n’y a aucun flux inactif !',
		'number_entries' => '%d articles',	// IGNORE
		'overview' => 'Vue d’ensemble',
		'percent_of_total' => '% du total',
		'repartition' => 'Répartition des articles: %s',
		'status_favorites' => 'favoris',
		'status_read' => 'lus',
		'status_total' => 'total',
		'status_unread' => 'non lus',
		'title' => 'Statistiques',
		'top_feed' => 'Les dix plus gros flux',
	),
	'system' => array(
		'_' => 'Configuration du système',
		'auto-update-url' => 'URL du service de mise à jour',
		'base-url' => array(
			'_' => 'URL de la racine',
			'recommendation' => 'Recommandation automatique : <kbd>%s</kbd>',
		),
		'cookie-duration' => array(
			'help' => 'en secondes',
			'number' => 'Durée avant expiration de la session',
		),
		'force_email_validation' => 'Forcer la validation des adresses email',
		'instance-name' => 'Nom de l’instance',
		'max-categories' => 'Limite de catégories par utilisateur',
		'max-feeds' => 'Limite de flux par utilisateur',
		'registration' => array(
			'number' => 'Nombre max de comptes',
			'select' => array(
				'label' => 'Formulaire d’inscription',
				'option' => array(
					'noform' => 'Désactivé : Pas de formulaire d’inscription',
					'nolimit' => 'Activé : Pas de limite au nombre d’utilisateurs',
					'setaccountsnumber' => 'Nombre d’utilisateurs limités',
				),
			),
			'status' => array(
				'disabled' => 'Formulaire désactivé',
				'enabled' => 'Formulaire activé',
			),
			'title' => 'Formulaire d’inscription utilisateur',
		),
		'sensitive-parameter' => 'Paramètre sensible. Éditez manuellement <kbd>./data/config.php</kbd>',
		'tos' => array(
			'disabled' => 'non renseigné',
			'enabled' => '<a href="./?a=tos">activées</a>',
			'help' => 'Comment <a href="https://freshrss.github.io/FreshRSS/en/admins/12_User_management.html#enable-terms-of-service-tos" target="_blank">activer les conditions d’utilisation</a>',
		),
		'websub' => array(
			'help' => 'À propos de <a href="https://freshrss.github.io/FreshRSS/fr/users/08_PubSubHubbub.html" target="_blank">WebSub</a>',
		),
	),
	'update' => array(
		'_' => 'Système de mise à jour',
		'apply' => 'Appliquer la mise à jour',
		'changelog' => 'Journal des modifications',
		'check' => 'Vérifier les mises à jour',
		'copiedFromURL' => 'update.php copié depuis %s vers ./data',
		'current_version' => 'Votre version actuelle',
		'last' => 'Dernière vérification',
		'loading' => 'Mise à jour en cours…',
		'none' => 'Aucune mise à jour à appliquer',
		'releaseChannel' => array(
			'_' => 'Canal de publication',
			'edge' => 'Publication continue (“edge”)',
			'latest' => 'Publication stable (“latest”)',
		),
		'title' => 'Système de mise à jour',
		'viaGit' => 'Mise à jour via git et GitHub.com démarrée',
	),
	'user' => array(
		'admin' => 'Administrateur',
		'article_count' => 'Articles',	// IGNORE
		'back_to_manage' => '← Revenir à la liste des utilisateurs',
		'create' => 'Créer un nouvel utilisateur',
		'database_size' => 'Volumétrie',
		'email' => 'Adresse électronique',
		'enabled' => 'Actif',
		'feed_count' => 'Flux',
		'is_admin' => 'Admin',
		'language' => 'Langue',
		'last_user_activity' => 'Dernière activité utilisateur',
		'list' => 'Liste des utilisateurs',
		'number' => '%d compte a déjà été créé',
		'numbers' => '%d comptes ont déjà été créés',
		'password_form' => 'Mot de passe<br /><small>(pour connexion par formulaire)</small>',
		'password_format' => '7 caractères minimum',
		'title' => 'Gestion des utilisateurs',
		'username' => 'Nom d’utilisateur',
	),
);
