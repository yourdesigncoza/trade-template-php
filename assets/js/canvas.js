/**
 * Canvas Drawing JavaScript
 */

function initializeCanvas() {
    const canvas = document.getElementById('drawingCanvas');
    const ctx = canvas.getContext('2d');
    const clearBtn = document.getElementById('clearCanvas');
    const drawingDataInput = document.getElementById('drawing_data');
    
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;
    
    // Set canvas size
    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = 200;
        
        // Redraw if there was existing data
        if (drawingDataInput.value) {
            const img = new Image();
            img.onload = function() {
                ctx.drawImage(img, 0, 0);
            };
            img.src = drawingDataInput.value;
        }
    }
    
    // Initialize canvas
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
    
    // Set drawing styles
    ctx.strokeStyle = '#000000';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    
    // Get mouse/touch position relative to canvas
    function getPosition(e) {
        const rect = canvas.getBoundingClientRect();
        let x, y;
        
        if (e.touches) {
            x = e.touches[0].clientX - rect.left;
            y = e.touches[0].clientY - rect.top;
        } else {
            x = e.clientX - rect.left;
            y = e.clientY - rect.top;
        }
        
        // Scale coordinates to canvas size
        x = x * (canvas.width / rect.width);
        y = y * (canvas.height / rect.height);
        
        return { x, y };
    }
    
    // Start drawing
    function startDrawing(e) {
        isDrawing = true;
        const pos = getPosition(e);
        lastX = pos.x;
        lastY = pos.y;
        
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        
        e.preventDefault();
    }
    
    // Draw
    function draw(e) {
        if (!isDrawing) return;
        
        const pos = getPosition(e);
        
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        
        lastX = pos.x;
        lastY = pos.y;
        
        e.preventDefault();
    }
    
    // Stop drawing
    function stopDrawing(e) {
        if (!isDrawing) return;
        
        isDrawing = false;
        saveCanvasData();
        
        e.preventDefault();
    }
    
    // Save canvas data to hidden input
    function saveCanvasData() {
        drawingDataInput.value = canvas.toDataURL();
    }
    
    // Clear canvas
    function clearCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        drawingDataInput.value = '';
    }
    
    // Mouse events
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    
    // Touch events
    canvas.addEventListener('touchstart', startDrawing);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', stopDrawing);
    canvas.addEventListener('touchcancel', stopDrawing);
    
    // Clear button
    clearBtn.addEventListener('click', clearCanvas);
    
    // Prevent scrolling when drawing on mobile
    canvas.addEventListener('touchstart', function(e) {
        if (e.touches.length === 1) {
            e.preventDefault();
        }
    });
}