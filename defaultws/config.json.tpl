{
	"global": {
		"secure": false,
		"advised_host": "localhost",
		"base_path": "",
		"secret": "%{APP_SECRET}%"
	},
	"prototype": {
		"enabled": false,
		"users": {
			"guest": ""
		}
	},
	"database": {
		"host": "127.0.0.1:3306",
		"user": "root",
		"charset": "utf8",
		"password": "",
		"name": ""
	},
	"session": {
		"cookie": "SFWSESSID",
		"regenerate_interval": 900,
		"configs": {
			"default": {
				"lifetime": 604800,
				"auto_update": true
			}
		}
	}
}
