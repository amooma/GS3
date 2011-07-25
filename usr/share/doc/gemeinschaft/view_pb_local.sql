CREATE VIEW asterisk.pb_local AS
	SELECT  
		users.firstname AS firstname,
		users.lastname AS lastname,  
		ast_sipfriends.name AS number
	FROM
		users, ast_sipfriends
	WHERE
		users.nobody_index IS NULL
	AND
		users.id = ast_sipfriends._user_id
	AND
		users.pb_hide = 0;

