---
allowed-tools:
  Bash(echo '' > /opt/lampp/htdocs/wecoza/wp-content/debug.log)
  Read(../../debug.log)
  echo "" > /opt/lampp/htdocs/wecoza/wp-content/debug.log 
description: Manage WordPress Logs
---

# Manage WordPress Logs

"Think Hard" then analyze and manage the WordPress debug log and WeCoza plugin console based on the requested action.

## Error Context:
- Debug log: `/opt/lampp/htdocs/wecoza/wp-content/debug.log`
- Console log: `/opt/lampp/htdocs/wecoza/wp-content/plugins/wecoza-agents-plugin/console.txt`


### Actions:
1. Check both log files for existence and size.
2. Analyze the last 100 lines of each file.
3. Categorize all errors by type and severity.
4. Show the 5 most recent critical errors.
5. Identify recurring error patterns.
6. Provide fix suggestions.
7. Create a Fix ToDos List based on your analysis.
8. Fix the errors found in the log analysis and update the Fix ToDos List as you proceed.
9. Clear the debug log after Fixes have completed.

## Todos:
- [ ] Check both log files for existence and size
- [ ] Analyze the last 100 lines of each file
- [ ] Categorize all errors by type and severity
- [ ] Show the 5 most recent critical errors
- [ ] Identify recurring error patterns
- [ ] Provide fix suggestions
- [ ] Create a ToDos list to fix the errors based on your analysis
- [ ] Fix the errors found in the log analysis and update the ToDos list
- [ ] Clear the debug log after Fixes have completed

Always provide a summary at the end with:
- Current status of both log files
- Number of issues found (if checking)
- Recommended next steps
- ToDos list (if applicable)

## Auto-clear after analysis:
After fixing the issues found in the log analysis automatically clear the debug log to keep it clean for future debugging:

```bash
# Clear debug log after analysis
> /opt/lampp/htdocs/wecoza/wp-content/debug.log
echo "Debug log cleared after analysis"
```
