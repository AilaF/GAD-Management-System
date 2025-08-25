// Enhanced Donut Chart Implementation with Hover
class DonutChart {
    constructor(canvasId, data) {
        this.canvas = document.getElementById(canvasId);
        this.ctx = this.canvas.getContext('2d');
        this.data = data;
        this.colors = {
            pending: '#ef4444',
            correction: '#f59e0b',
            completed: '#10b981'
        };
        this.hoverColors = {
            pending: '#dc2626',
            correction: '#d97706',
            completed: '#059669'
        };
        
        this.hoveredSegment = -1;
        this.segments = []; // Store segment data for hit detection
        
        // Set up high DPI canvas
        this.setupCanvas();
        this.setupEventListeners();
        this.draw();
        this.createTooltip();
    }

    setupCanvas() {
        const rect = this.canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        
        this.canvas.width = rect.width * dpr;
        this.canvas.height = rect.height * dpr;
        
        this.ctx.scale(dpr, dpr);
        this.canvas.style.width = rect.width + 'px';
        this.canvas.style.height = rect.height + 'px';
    }

    createTooltip() {
        // Create tooltip element
        this.tooltip = document.createElement('div');
        this.tooltip.style.cssText = `
            position: absolute;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 1000;
            white-space: nowrap;
        `;
        document.body.appendChild(this.tooltip);
    }

    setupEventListeners() {
        this.canvas.addEventListener('mousemove', (e) => this.handleMouseMove(e));
        this.canvas.addEventListener('mouseleave', () => this.handleMouseLeave());
        this.canvas.style.cursor = 'default';
    }

    handleMouseMove(e) {
        const rect = this.canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const hoveredIndex = this.getSegmentAtPoint(x, y);
        
        if (hoveredIndex !== this.hoveredSegment) {
            this.hoveredSegment = hoveredIndex;
            this.draw();
            
            if (hoveredIndex >= 0) {
                this.canvas.style.cursor = 'pointer';
                this.showTooltip(e, this.data[hoveredIndex]);
            } else {
                this.canvas.style.cursor = 'default';
                this.hideTooltip();
            }
        } else if (hoveredIndex >= 0) {
            this.updateTooltipPosition(e);
        }
    }

    handleMouseLeave() {
        if (this.hoveredSegment >= 0) {
            this.hoveredSegment = -1;
            this.draw();
            this.canvas.style.cursor = 'default';
            this.hideTooltip();
        }
    }

    showTooltip(e, data) {
        const total = this.getTotalValue();
        const percentage = ((data.value / total) * 100).toFixed(1);
        
        this.tooltip.innerHTML = `
            <strong>${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</strong><br>
            Value: ${data.value}<br>
            Percentage: ${percentage}%
        `;
        
        this.tooltip.style.opacity = '1';
        this.updateTooltipPosition(e);
    }

    updateTooltipPosition(e) {
        const tooltipRect = this.tooltip.getBoundingClientRect();
        let left = e.clientX + 10;
        let top = e.clientY - 10;
        
        // Keep tooltip within viewport
        if (left + tooltipRect.width > window.innerWidth) {
            left = e.clientX - tooltipRect.width - 10;
        }
        if (top < 0) {
            top = e.clientY + 20;
        }
        
        this.tooltip.style.left = left + 'px';
        this.tooltip.style.top = top + 'px';
    }

    hideTooltip() {
        this.tooltip.style.opacity = '0';
    }

    getSegmentAtPoint(x, y) {
        const rect = this.canvas.getBoundingClientRect();
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const outerRadius = Math.min(rect.width, rect.height) / 2 - 10;
        const innerRadius = outerRadius * 0.6;
        
        // Calculate distance from center
        const dx = x - centerX;
        const dy = y - centerY;
        const distance = Math.sqrt(dx * dx + dy * dy);
        
        // Check if point is within donut ring
        if (distance < innerRadius || distance > outerRadius) {
            return -1;
        }
        
        // Calculate angle
        let angle = Math.atan2(dy, dx);
        angle = (angle + Math.PI / 2 + 2 * Math.PI) % (2 * Math.PI);
        
        // Find which segment this angle belongs to
        let currentAngle = 0;
        const total = this.getTotalValue();
        
        for (let i = 0; i < this.data.length; i++) {
            const segmentAngle = (this.data[i].value / total) * 2 * Math.PI;
            if (angle >= currentAngle && angle < currentAngle + segmentAngle) {
                return i;
            }
            currentAngle += segmentAngle;
        }
        
        return -1;
    }

    draw() {
        // Clear canvas
        const rect = this.canvas.getBoundingClientRect();
        this.ctx.clearRect(0, 0, rect.width, rect.height);
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const outerRadius = Math.min(rect.width, rect.height) / 2 - 10;
        const innerRadius = outerRadius * 0.6;
        
        let currentAngle = -Math.PI / 2;
        
        this.data.forEach((item, index) => {
            const sliceAngle = (item.value / this.getTotalValue()) * 2 * Math.PI;
            const isHovered = index === this.hoveredSegment;
            
            // Use hover color if this segment is hovered
            const color = isHovered ? this.hoverColors[item.status] : this.colors[item.status];
            
            // Slightly expand segment if hovered
            const segmentOuterRadius = isHovered ? outerRadius + 5 : outerRadius;
            
            this.ctx.beginPath();
            this.ctx.arc(centerX, centerY, segmentOuterRadius, currentAngle, currentAngle + sliceAngle);
            this.ctx.arc(centerX, centerY, innerRadius, currentAngle + sliceAngle, currentAngle, true);
            this.ctx.closePath();
            
            this.ctx.fillStyle = color;
            this.ctx.fill();
            
            // Add subtle shadow for hovered segment
            if (isHovered) {
                this.ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
                this.ctx.shadowBlur = 10;
                this.ctx.shadowOffsetX = 2;
                this.ctx.shadowOffsetY = 2;
                this.ctx.fill();
                
                // Reset shadow
                this.ctx.shadowColor = 'transparent';
                this.ctx.shadowBlur = 0;
                this.ctx.shadowOffsetX = 0;
                this.ctx.shadowOffsetY = 0;
            }
            
            currentAngle += sliceAngle;
        });
    }

    getTotalValue() {
        return this.data.reduce((sum, item) => sum + item.value, 0);
    }

    // Method to update data and redraw
    updateData(newData) {
        this.data = newData;
        this.draw();
    }

    // Cleanup method
    destroy() {
        if (this.tooltip && this.tooltip.parentNode) {
            this.tooltip.parentNode.removeChild(this.tooltip);
        }
    }
}

// Initialize the chart when the page loads
document.addEventListener('DOMContentLoaded', function() {
    const chartData = [
        { status: 'pending', value: 28 },
        { status: 'correction', value: 12 },
        { status: 'completed', value: 6 }
    ];

    new DonutChart('donutChart', chartData);
});

// Handle navigation clicks
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function(e) {
        const href = this.getAttribute('href');

        // Only prevent default if href is "#" (placeholder)
        if (href === "#") {
            e.preventDefault();
        }

        // Remove active class from all items
        document.querySelectorAll('.nav-item').forEach(nav => {
            nav.classList.remove('active');
        });

        // Add active class to clicked item
        this.classList.add('active');
    });
});

// Handle notification click
document.querySelector('.notification-icon').addEventListener('click', function() {
    alert('You have new notifications!');
});

// Handle admin info click
document.querySelector('.admin-info').addEventListener('click', function() {
    alert('Admin menu would open here');
});