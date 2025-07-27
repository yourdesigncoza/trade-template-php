/**
 * Trade Form JavaScript
 */

let allEntryCriteriaChecked = false;
let strategyData = {};

function initializeTradeForm() {
    // Get form elements
    const strategySelect = document.getElementById('strategy_id');
    const tradeTakenCheckbox = document.getElementById('tradeTaken');
    const submitBtn = document.getElementById('submitBtn');
    const entryCriteriaSection = document.getElementById('entryCriteriaSection');
    const exitCriteriaSection = document.getElementById('exitCriteriaSection');
    const invalidationsSection = document.getElementById('invalidationsSection');
    const priceFields = document.getElementById('priceFields');
    const missedReasonField = document.getElementById('missedReasonField');
    
    // Price input elements for R-multiple calculation
    const entryPrice = document.getElementById('entry_price');
    const stopLoss = document.getElementById('stop_loss_price');
    const exitPrice = document.getElementById('exit_price');
    const rMultipleDisplay = document.getElementById('rMultipleDisplay');
    
    // Strategy change handler
    strategySelect.addEventListener('change', async function() {
        const strategyId = this.value;
        
        if (!strategyId) {
            entryCriteriaSection.classList.add('hidden');
            exitCriteriaSection.classList.add('hidden');
            invalidationsSection.classList.add('hidden');
            submitBtn.disabled = true;
            return;
        }
        
        // Fetch strategy details
        try {
            const response = await fetch(`api/get-strategy-details.php?id=${strategyId}`);
            const data = await response.json();
            
            if (data.success) {
                strategyData = data.strategy;
                populateChecklists(data.strategy);
                entryCriteriaSection.classList.remove('hidden');
                exitCriteriaSection.classList.remove('hidden');
                
                // Show invalidations if any exist
                if (data.strategy.invalidations && data.strategy.invalidations.length > 0) {
                    invalidationsSection.classList.remove('hidden');
                }
                
                checkFormValidity();
            }
        } catch (error) {
            console.error('Error fetching strategy details:', error);
        }
    });
    
    // Trade taken toggle handler
    tradeTakenCheckbox.addEventListener('change', function() {
        if (this.checked) {
            priceFields.classList.remove('hidden');
            missedReasonField.classList.add('hidden');
            
            // Make price fields required
            entryPrice.required = true;
            stopLoss.required = true;
            exitPrice.required = true;
            document.getElementById('risk_percent').required = true;
            document.getElementById('missed_reason').required = false;
        } else {
            priceFields.classList.add('hidden');
            missedReasonField.classList.remove('hidden');
            
            // Make price fields not required
            entryPrice.required = false;
            stopLoss.required = false;
            exitPrice.required = false;
            document.getElementById('risk_percent').required = false;
            document.getElementById('missed_reason').required = true;
        }
        
        checkFormValidity();
    });
    
    // R-Multiple calculation
    function calculateRMultiple() {
        const entry = parseFloat(entryPrice.value);
        const stop = parseFloat(stopLoss.value);
        const exit = parseFloat(exitPrice.value);
        
        if (isNaN(entry) || isNaN(stop) || isNaN(exit)) {
            rMultipleDisplay.textContent = '--';
            return;
        }
        
        if (entry === stop) {
            rMultipleDisplay.textContent = '--';
            return;
        }
        
        const risk = Math.abs(entry - stop);
        let reward = exit - entry;
        
        // For short trades, reverse the calculation
        if (stop > entry) {
            reward = entry - exit;
        }
        
        const rMultiple = (reward / risk).toFixed(2);
        rMultipleDisplay.textContent = rMultiple;
        
        // Color code based on profit/loss
        if (rMultiple >= 0) {
            rMultipleDisplay.classList.remove('text-red-600');
            rMultipleDisplay.classList.add('text-green-600');
        } else {
            rMultipleDisplay.classList.remove('text-green-600');
            rMultipleDisplay.classList.add('text-red-600');
        }
    }
    
    // Add event listeners for R-multiple calculation
    entryPrice.addEventListener('input', calculateRMultiple);
    stopLoss.addEventListener('input', calculateRMultiple);
    exitPrice.addEventListener('input', calculateRMultiple);
    
    // Populate checklists
    function populateChecklists(strategy) {
        // Entry criteria
        const entryCriteriaList = document.getElementById('entryCriteriaList');
        entryCriteriaList.innerHTML = '';
        
        if (strategy.entry_criteria && strategy.entry_criteria.length > 0) {
            strategy.entry_criteria.forEach(criteria => {
                const div = document.createElement('div');
                div.className = 'flex items-start';
                div.innerHTML = `
                    <input type="checkbox" 
                           name="entry_criteria[]" 
                           value="${criteria.id}" 
                           id="entry_${criteria.id}" 
                           class="mt-1 entry-criteria-checkbox">
                    <label for="entry_${criteria.id}" class="ml-3 cursor-pointer">
                        <span class="font-medium">${escapeHtml(criteria.label)}</span>
                        ${criteria.description ? `<p class="text-sm text-gray-500">${escapeHtml(criteria.description)}</p>` : ''}
                    </label>
                `;
                entryCriteriaList.appendChild(div);
            });
            
            // Add event listeners to entry checkboxes
            document.querySelectorAll('.entry-criteria-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', checkAllEntryCriteria);
            });
        }
        
        // Exit criteria
        const exitCriteriaList = document.getElementById('exitCriteriaList');
        exitCriteriaList.innerHTML = '';
        
        if (strategy.exit_criteria && strategy.exit_criteria.length > 0) {
            strategy.exit_criteria.forEach(criteria => {
                const div = document.createElement('div');
                div.className = 'flex items-start';
                div.innerHTML = `
                    <input type="checkbox" 
                           name="exit_criteria[]" 
                           value="${criteria.id}" 
                           id="exit_${criteria.id}" 
                           class="mt-1">
                    <label for="exit_${criteria.id}" class="ml-3 cursor-pointer">
                        <span class="font-medium">${escapeHtml(criteria.label)}</span>
                        ${criteria.description ? `<p class="text-sm text-gray-500">${escapeHtml(criteria.description)}</p>` : ''}
                    </label>
                `;
                exitCriteriaList.appendChild(div);
            });
        }
        
        // Invalidations
        const invalidationsList = document.getElementById('invalidationsList');
        invalidationsList.innerHTML = '';
        
        if (strategy.invalidations && strategy.invalidations.length > 0) {
            strategy.invalidations.forEach(invalidation => {
                const div = document.createElement('div');
                div.className = 'flex items-start';
                div.innerHTML = `
                    <input type="checkbox" 
                           name="invalidations[]" 
                           value="${invalidation.id}" 
                           id="inv_${invalidation.id}" 
                           class="mt-1">
                    <label for="inv_${invalidation.id}" class="ml-3 cursor-pointer">
                        <span class="font-medium">${invalidation.code}. ${escapeHtml(invalidation.label)}</span>
                        ${invalidation.reason ? `<p class="text-sm text-gray-500">${escapeHtml(invalidation.reason)}</p>` : ''}
                    </label>
                `;
                invalidationsList.appendChild(div);
            });
        }
    }
    
    // Check if all entry criteria are checked
    function checkAllEntryCriteria() {
        const checkboxes = document.querySelectorAll('.entry-criteria-checkbox');
        allEntryCriteriaChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkFormValidity();
    }
    
    // Check overall form validity
    function checkFormValidity() {
        const strategySelected = strategySelect.value !== '';
        const tradeTaken = tradeTakenCheckbox.checked;
        
        if (tradeTaken) {
            // For taken trades, all entry criteria must be checked
            submitBtn.disabled = !strategySelected || !allEntryCriteriaChecked;
        } else {
            // For missed trades, just need strategy selected
            submitBtn.disabled = !strategySelected;
        }
    }
    
    // Escape HTML for security
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Initialize on load
    if (strategySelect.value) {
        strategySelect.dispatchEvent(new Event('change'));
    }
}