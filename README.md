# dir-deploy
Primitive tool for selecting specific files, dirs, resources and copying them into destination directory

It is based on two steps

1. creates list of operations, based on deployment list file, which defines including and excluding proper files and directories
2. copies resulting files into specified directory

This tool might be useful for very basic deployment scenarios, for example "take everything from this directory except .git dir and also skip this custom config file I'm using for local development"

## usage 

Prepare text file with list of your files or directories to deploy. Prepend each entry with proper symbol:

\*	adds directory and everything inside (recursively) 

D	adds just a directory (useful for /tmp, /log etc.)

\+	adds single file or directory (no recursion)

\- 	excludes single file or directory (we've added all .php files but we don't want to include our config.php file)

*func*	executes custom function, for example - processing config file and removing our local database credentials, possibly including more specific ones