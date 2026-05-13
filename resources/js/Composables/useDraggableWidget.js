import { ref, computed, onUnmounted } from 'vue';

export function useDraggableWidget(initialPositionClass = 'bottom-6 right-6') {
    const position = ref({ x: null, y: null });
    const isDragging = ref(false);
    const hasMoved = ref(false);
    
    let startX = 0;
    let startY = 0;
    let initialClientX = 0;
    let initialClientY = 0;

    const widgetStyle = computed(() => {
        if (position.value.x === null) {
            return {}; // Use default classes
        }
        return {
            top: position.value.y + 'px',
            left: position.value.x + 'px',
            bottom: 'auto',
            right: 'auto',
            margin: '0', // Remove any margins that might affect positioning
        };
    });

    const widgetClasses = computed(() => {
        if (position.value.x === null) {
            return `fixed ${initialPositionClass} z-[100]`;
        }
        return 'fixed z-[100]';
    });

    const startDrag = (e) => {
        // Prevent dragging if clicking on an interactive element inside the header/button
        // unless it's explicitly allowed.
        const target = e.target;
        if (target.tagName.toLowerCase() === 'textarea' || target.tagName.toLowerCase() === 'input') {
            return;
        }

        isDragging.value = true;
        hasMoved.value = false;
        
        initialClientX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
        initialClientY = e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;

        const widgetEl = e.currentTarget.closest('.draggable-widget-container');
        if (widgetEl) {
            const rect = widgetEl.getBoundingClientRect();
            
            // If it hasn't been moved yet, we need to convert its position from bottom/right to top/left
            if (position.value.x === null) {
                position.value = {
                    x: rect.left,
                    y: rect.top
                };
            }

            startX = position.value.x;
            startY = position.value.y;
        }

        document.addEventListener('mousemove', onDrag);
        document.addEventListener('mouseup', stopDrag);
        document.addEventListener('touchmove', onDrag, { passive: false });
        document.addEventListener('touchend', stopDrag);
    };

    const onDrag = (e) => {
        if (!isDragging.value) return;

        const clientX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
        const clientY = e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;

        const dx = clientX - initialClientX;
        const dy = clientY - initialClientY;

        if (Math.abs(dx) > 3 || Math.abs(dy) > 3) {
            hasMoved.value = true;
            if (e.cancelable) e.preventDefault(); // Prevent scrolling on touch devices while dragging
        }

        let newX = startX + dx;
        let newY = startY + dy;

        // Keep within window bounds
        const maxX = window.innerWidth - 60; // Approximate width of button
        const maxY = window.innerHeight - 60;

        newX = Math.max(0, Math.min(newX, maxX));
        newY = Math.max(0, Math.min(newY, maxY));

        position.value = {
            x: newX,
            y: newY
        };
    };

    const stopDrag = () => {
        isDragging.value = false;
        document.removeEventListener('mousemove', onDrag);
        document.removeEventListener('mouseup', stopDrag);
        document.removeEventListener('touchmove', onDrag);
        document.removeEventListener('touchend', stopDrag);
        
        // Reset hasMoved after a short delay so click handlers can check it
        setTimeout(() => {
            hasMoved.value = false;
        }, 50);
    };

    onUnmounted(() => {
        stopDrag();
    });

    return {
        position,
        isDragging,
        hasMoved,
        widgetStyle,
        widgetClasses,
        startDrag
    };
}
