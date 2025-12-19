jQuery(document).ready(function($) {
    
    // Config
    const CHUNK_SIZE = 20; // Process 20 posts at a time
    let csvData = [];
    let totalRows = 0;
    let processedRows = 0;
    
    // UI Elements
    const $fileInput = $('#msm-csv-file');
    const $startBtn = $('#msm-start-import');
    const $progressWrapper = $('.msm-progress-wrapper');
    const $progressBar = $('.msm-progress-bar');
    const $logBox = $('.msm-log-box');
    const $statsBox = $('.msm-stats');
    
    // Stats Elements
    const $statTotal = $('#msm-stat-total');
    const $statSuccess = $('#msm-stat-success');
    const $statFailed = $('#msm-stat-failed');

    let countSuccess = 0;
    let countFailed = 0;

    // 1. Read CSV File
    $startBtn.on('click', function(e) {
        e.preventDefault();
        
        const file = $fileInput[0].files[0];
        if (!file) {
            alert('Please select a CSV file first.');
            return;
        }

        // Reset UI
        $startBtn.prop('disabled', true).text('Processing...');
        $progressWrapper.show();
        $logBox.show().html('<p>Reading file...</p>');
        $statsBox.css('display', 'grid'); // Grid needs explicit display type
        processedRows = 0;
        countSuccess = 0;
        countFailed = 0;
        updateStats();

        // Parse File
        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            csvData = parseCSV(text);
            totalRows = csvData.length;
            
            if (totalRows === 0) {
                log("❌ Error: CSV is empty or invalid.", "error");
                $startBtn.prop('disabled', false).text('Start Import');
                return;
            }

            log(`✅ File Loaded. Found ${totalRows} rows. Starting Batch Process...`);
            $statTotal.text(totalRows);
            
            // Start the Loop
            processNextBatch();
        };
        reader.readAsText(file);
    });

    // 2. The Batch Loop
    function processNextBatch() {
        if (processedRows >= totalRows) {
            finishImport();
            return;
        }

        // Slice a chunk of data
        const chunk = csvData.slice(processedRows, processedRows + CHUNK_SIZE);

        // Send to Server
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'msm_process_batch',
                nonce: msm_vars.nonce,
                rows: chunk
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Update Counts
                    countSuccess += data.updated;
                    countFailed += data.failed;
                    processedRows += chunk.length;
                    
                    // Update Log
                    data.log.forEach(msg => {
                        const type = msg.includes('❌') ? 'error' : (msg.includes('⚠️') ? 'warn' : 'normal');
                        log(msg, type);
                    });

                    // Update UI
                    const percent = Math.min(100, Math.round((processedRows / totalRows) * 100));
                    $progressBar.css('width', percent + '%');
                    updateStats();

                    // Trigger Next Batch
                    processNextBatch();

                } else {
                    log("❌ Server Error: " + response.data, "error");
                    $startBtn.prop('disabled', false).text('Retry');
                }
            },
            error: function() {
                log("❌ Fatal AJAX Error. Stopping.", "error");
                $startBtn.prop('disabled', false).text('Retry');
            }
        });
    }

    function finishImport() {
        $progressBar.css('width', '100%').css('background-color', '#46b450'); // Green
        log("🏁 Import Complete!", "normal");
        $startBtn.prop('disabled', false).text('Import Complete');
        alert("Import Complete!");
    }

    // Helper: Simple CSV Parser
    function parseCSV(text) {
        const lines = text.split('\n');
        const headers = lines[0].split(',').map(h => h.trim().replace(/"/g, ''));
        const result = [];

        for (let i = 1; i < lines.length; i++) {
            if (!lines[i].trim()) continue;
            
            // Handle commas inside quotes (basic regex split)
            const matches = lines[i].match(/(".*?"|[^",\s]+)(?=\s*,|\s*$)/g);
            // Fallback for simple split if regex fails or simple CSV
            const row = lines[i].split(','); 
            
            if (row.length < headers.length) continue; // Skip broken rows

            let obj = {};
            headers.forEach((h, index) => {
                let val = row[index] ? row[index].trim() : '';
                // Remove quotes
                val = val.replace(/^"|"$/g, '');
                obj[h] = val;
            });
            result.push(obj);
        }
        return result;
    }

    function log(msg, type = 'normal') {
        let cssClass = '';
        if (type === 'error') cssClass = 'msm-log-error';
        if (type === 'warn') cssClass = 'msm-log-warn';
        
        $logBox.append(`<p class="${cssClass}">${msg}</p>`);
        $logBox.scrollTop($logBox[0].scrollHeight); // Auto scroll
    }

    function updateStats() {
        $statSuccess.text(countSuccess);
        $statFailed.text(countFailed);
    }
});