# Manage WordPress Logs

Analyze and manage the WordPress debug log and WeCoza plugin console based on the requested action.

## Target files:
- Debug log: `/opt/lampp/htdocs/wecoza/wp-content/debug.log`
- Console log: `/opt/lampp/htdocs/wecoza/wp-content/plugins/wecoza-classes-plugin/console.txt`

## Action requested: $ARGUMENTS

Based on the arguments provided, perform one of these actions:

### If no arguments or "check":
1. Check both log files for existence and size
2. Analyze the last 100 lines of each file
3. Categorize all errors by type and severity
4. Show the 5 most recent critical errors
5. Identify recurring error patterns
6. Provide fix suggestions

### If "clear" or "clean":
1. Clear the log files (truncate to 0 bytes)
2. Confirm the operation was successful

### If "backup":
1. Create timestamped backups of both logs
2. Show backup file locations
3. Display current log sizes

### If "tail [number]":
1. Show the last N lines from both files
2. Highlight any errors or warnings
3. Default to 20 lines if no number specified

### If "errors-only":
1. Extract only error lines from both logs
2. Group by error type
3. Show frequency of each error

### If "watch":
1. Provide a command to continuously monitor the logs
2. Set up a tail -f command for real-time monitoring

Always provide a summary at the end with:
- Current status of both log files
- Number of issues found (if checking)
- Recommended next steps