---
allowed-tools:
  Bash(git status:*),
  Bash(git diff HEAD:*),
  Bash(git branch --show-current:*),
  Bash(git log --oneline -10:*)
description: Extract only checked items from the checklist, then include the Git context and create a single git commit.
---

# Command definition

```bash
echo 'I have the following request or task: #$ARGUMENTS and I need you to ThinkHard about it after extracting the completed checklist items from the context.'
```

## Instructions

1. Extract **only** the checklist entries marked `[x]`.
2. Ignore all unchecked items `[ ]` do **not** mention or summarize them in any way.  
3. For each extracted item, summarize **only** the files or code directly related to that item.  
4. Do not reference any IDs or files that weren’t part of a checked entry.  
5. After steps 1–4, include the full Git context by running these slash-commands:

   ```text
   /bash git status
   /bash git diff HEAD
   /bash git branch --show-current
   /bash git log --oneline -10

## Context

When you read this, print  
> **I see you BOSS!**  
so I know the file loaded correctly.

### Components to reference if checked

- [x] #class-learners-container  
- [x] #exam-learners-list  
- [ ] #schedule-update-end-date-container  
- [ ] #schedule-start-date  
- [ ] #schedule-end-date  
- [ ] #schedule-update-end-date-btn  

### Assets to reference if checked

- [x] schema/classes_schema.sql  
- [ ] captured-exam-learners.json  
- [x] captured.json  

### View Files to examine if checked / reference

- [x] app/Views/components/class-capture-partials/update-class.php  
- [ ] app/Views/components/class-capture-form.view.php  
- [x] app/Views/components/class-capture-partials/create-class.php  
- [ ] app/Views/components/single-class-display.view.php  

### Screenshots to reference if checked

- [ ] ![alt text](<Screenshot from 2025-07-06 16-11-21.png>)  
- [ ] ![alt text](<Screenshot from 2025-07-06 16-13-22.png>)  
- [x] ![alt text](<Screenshot from 2025-07-07 19-36-33.png>)

### Code Snippets to reference if checked

- [ ] Section

  ```html
  <!-- PHP code to reference -->
  <input type="date" id="schedule_end_date" name="schedule_end_date" class="form-control readonly-field" placeholder="YYYY-MM-DD" required>
  ```

- [ ] Section

  ```js
  // JavaScript code to reference
  document.getElementById('schedule_end_date').value = '';
  ```

---

## GIT Context

After filtering to only the checked items, run these commands to capture your Git state:

```text
/bash git status
/bash git diff HEAD
/bash git branch --show-current
/bash git log --oneline -10
```

_Show both each command and its resulting output snippets so we can tie the work back to specific commits and changes._

## Do a Self Reflection

If you get to a conclusion similar to below, then you are wrong you were supposed to **totally ignore the unchecked items** and only reference the checked ones.

  ```bash
  What We Should Do Next
  Based on the commit progression and unchecked items, I recommend:
```


