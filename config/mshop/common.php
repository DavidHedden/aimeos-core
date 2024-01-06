<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org], 2018-2024
 */


return [
	'manager' => [
		'decorators' => [
			'default' => [
				'Depth' => 'Depth',
				'Lazy' => 'Lazy',
			],
		],

		// generic SQL statements

		'delete' => [
			'ansi' => '
				DELETE FROM ":table"
				WHERE :cond AND "siteid" LIKE ?
			'
		],
		'insert' => [
			'ansi' => '
				INSERT INTO ":table" (
					:names
					"mtime", "editor", "siteid", "ctime"
				) VALUES (
					:values
					?, ?, ?, ?
				)
			'
		],
		'update' => [
			'ansi' => '
				UPDATE ":table"
				SET :names "mtime" = ?, "editor" = ?
				WHERE "siteid" LIKE ? AND "id" = ?
			'
		],
		'search' => [
			'ansi' => '
				SELECT :columns
				FROM ":table"
				:joins
				WHERE :cond
				GROUP BY :group
				ORDER BY :order
				OFFSET :start ROWS FETCH NEXT :size ROWS ONLY
			',
			'mysql' => '
				SELECT :columns
				FROM ":table"
				:joins
				WHERE :cond
				GROUP BY :group
				ORDER BY :order
				LIMIT :size OFFSET :start
			'
		],
		'count' => [
			'ansi' => '
				SELECT COUNT(*) AS "count"
				FROM (
					SELECT "id"
					FROM ":table"
					:joins
					WHERE :cond
					GROUP BY "id"
					ORDER BY "id"
					OFFSET 0 ROWS FETCH NEXT 10000 ROWS ONLY
				) AS list
			',
			'mysql' => '
				SELECT COUNT(*) AS "count"
				FROM (
					SELECT "id"
					FROM ":table"
					:joins
					WHERE :cond
					GROUP BY "id"
					ORDER BY "id"
					LIMIT 10000 OFFSET 0
				) AS list
			'
		],
		'newid' => [
			'mysql' => 'SELECT LAST_INSERT_ID()',
			'pgsql' => 'SELECT lastval()',
			'sqlsrv' => 'SELECT @@IDENTITY',
		],
	],
];
