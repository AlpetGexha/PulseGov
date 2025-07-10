/* Marker Recolor Script
 * Run this in browser console on a page with a canvas element to generate colored markers
 */

function createColoredMarker(color) {
    const canvas = document.createElement('canvas');
    canvas.width = 25;
    canvas.height = 41;
    const ctx = canvas.getContext('2d');
    
    // Draw marker shape
    ctx.beginPath();
    ctx.moveTo(12.5, 41);
    ctx.lineTo(0, 15);
    ctx.quadraticCurveTo(0, 0, 12.5, 0);
    ctx.quadraticCurveTo(25, 0, 25, 15);
    ctx.lineTo(12.5, 41);
    ctx.closePath();
    
    // Fill with color
    ctx.fillStyle = color;
    ctx.fill();
    
    // Add border
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 1;
    ctx.stroke();
    
    // Add inner circle
    ctx.beginPath();
    ctx.arc(12.5, 15, 6, 0, Math.PI * 2);
    ctx.fillStyle = '#fff';
    ctx.fill();
    ctx.stroke();
    
    return canvas.toDataURL();
}

const colors = {
    red: '#dc2626',    // For critical
    orange: '#f97316', // For high
    yellow: '#facc15', // For medium
    green: '#22c55e'   // For low
};

// Generate markers for each color
Object.entries(colors).forEach(([name, color]) => {
    const dataUrl = createColoredMarker(color);
    console.log(`${name}-marker:`, dataUrl);
});
