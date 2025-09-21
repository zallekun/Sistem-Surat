<script>
document.addEventListener("DOMContentLoaded", function() {
    // Force all buttons to be visible
    const buttons = document.querySelectorAll("button, a.inline-flex");
    buttons.forEach(button => {
        button.style.opacity = "1";
        button.style.visibility = "visible";
        // Remove any hover-only classes
        if (button.classList.contains("opacity-0")) {
            button.classList.remove("opacity-0");
        }
        if (button.classList.contains("hover:opacity-100")) {
            button.classList.remove("hover:opacity-100");
        }
    });
    
    // Also check after Livewire updates
    if (typeof Livewire !== "undefined") {
        Livewire.hook("message.processed", () => {
            const buttons = document.querySelectorAll("button, a.inline-flex");
            buttons.forEach(button => {
                button.style.opacity = "1";
                button.style.visibility = "visible";
            });
        });
    }
});
</script>