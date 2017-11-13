# heroku dyno executes this file on startup
# see https://devcenter.heroku.com/articles/dynos#the-profile-file

# here is the example input string
#CLEARDB_DATABASE_URL="mysql://2949ed54ccb:bd5ef@us-cdbr-iron-east-05.cleardb.net/heroku_91bb56405b70f3a?reconnect=true"

STRIP_PROTOCOL="${CLEARDB_DATABASE_URL#*//}"
# '2949ed54ccb:bd5ef@us-cdbr-iron-east-05.cleardb.net/heroku_91bb56405b70f3a?reconnect=true'
STRIP_USERNAME="${STRIP_PROTOCOL#*:}"
# 'bd5ef@us-cdbr-iron-east-05.cleardb.net/heroku_91bb56405b70f3a?reconnect=true'
STRIP_PASSWORD="${STRIP_USERNAME#*@}"
# 'us-cdbr-iron-east-05.cleardb.net/heroku_91bb56405b70f3a?reconnect=true'
DATABASE_HOST="${STRIP_PASSWORD%%/*}"
STRIP_HOST="${STRIP_PASSWORD#*/}"
DATABASE_NAME="${STRIP_HOST%%\?*}"

export DATABASE_URL="mysql:host=${DATABASE_HOST};dbname=${DATABASE_NAME}"
# '2949ed54ccb'
export DATABASE_USER=${STRIP_PROTOCOL%%:*}
# 'bd5ef'
export DATABASE_PWD=${STRIP_USERNAME%@*}
