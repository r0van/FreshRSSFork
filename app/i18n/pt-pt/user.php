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
	'email' => array(
		'feedback' => array(
			'invalid' => 'Endereço de email inválido',
			'required' => 'O endereço de email é necessário',
		),
		'validation' => array(
			'change_email' => 'Pode mudar seu endereço de email <a href="%s">na página do perfil</a>.',
			'email_sent_to' => 'Enviamos um email para <strong>%s</strong>. Por favor, siga as instruções contidas nele para verificar sua conta.',
			'feedback' => array(
				'email_failed' => 'Não foi possível enviar um email devido a um erro de configuração no servidor.',
				'email_sent' => 'Um email foi enviado para o seu endereço',
				'error' => 'Falha na verificação do endereço de email',
				'ok' => 'O endereço de email foi verificado com sucesso.',
				'unnecessary' => 'Esse endereço de email já foi verificado.',
				'wrong_token' => 'A verificação do endereço de email falhou por causa do token incorreto.',
			),
			'need_to' => 'Para poder utilizar o %s, deve verificar seu endereço de email.',
			'resend_email' => 'Reenviar o email',
			'title' => 'Validação do endereço de email',
		),
	),
	'mailer' => array(
		'email_need_validation' => array(
			'body' => 'Registrou no %s. Mas ainda é necessário verificar seu endereço de email. Para isso, basta seguir o link:',
			'title' => 'Precisa verificar a conta',
			'welcome' => 'Bem vindo %s,',
		),
	),
	'password' => array(
		'invalid' => 'Senha incorreta',
	),
	'tos' => array(
		'feedback' => array(
			'invalid' => 'Para se registrar, tem que aceitar os Termos do serviço.',
		),
	),
	'username' => array(
		'invalid' => 'Nome de utilizador inválido.',
		'taken' => 'O nome de utilizador %s já está sendo utilizado',
	),
);
