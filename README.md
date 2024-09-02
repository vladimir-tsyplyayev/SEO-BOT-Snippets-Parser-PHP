# Snippets Parser

### Install

The script works on PHP. For the script to work, files and folders must be placed on the server.

The folder in which the script is located must have the rights (attributes) set to 777.

The folders ***snippets/***, ***utilites/keywords*** and ***utilites/keywords/cookie/*** must also have the rights (attributes) set to 777.

### Settings

1. In the ***settings/*** folder are all settings;

2. To the ***settings/keywords.txt*** file add keywords;
```
dollar
buy dollar
buy dollar online
```
3. To the ***settings/proxy.txt*** file add a proxy;
```
74.53.15.140:3129
213.171.70.243:8080
108.161.130.154:3128
```
4. TO the ***settings/se.txt*** file set the search engine URL with the domain zone, for example:
```
google.ca
google.com
google.ru
google.com.ua
aol.de
aol.com
aol.co.uk
```
5. In the ***settings/treads.txt*** file, set the number of threads;
```
3
```
6. In the ***settings/language.txt*** file, set the parsing language, for example:
```
en
ru
de
fr
es
```
7. In the ***settings/limit.txt*** file, set the maximum snippet length;
```
3000
```

### Run Script

1. The script works in the browser. To run, open ***http://path_to_script/index.php***

2. Do not close the browser until the end of the work.

3. The results with parsed snippets are saved in separate files with the key name in the ***snippets/*** folder

Have Fun! 

(c) digg 2015
