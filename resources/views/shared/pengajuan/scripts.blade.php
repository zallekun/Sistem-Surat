<script>
function showApproveConfirm() {
    document.getElementById('approveModal').classList.remove('hidden');
    document.getElementById('approveModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
    document.getElementById('approveModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
}

function showRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
    document.getElementById('rejectionReason').focus();
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
    document.getElementById('rejectionReason').value = '';
    document.body.style.overflow = 'auto';
}

function processPengajuan(action) {
    let data = { action: action };
    
    if (action === 'reject') {
        const reason = document.getElementById('rejectionReason').value.trim();
        if (!reason) {
            alert('Alasan penolakan harus diisi!');
            return;
        }
        if (reason.length < 10) {
            alert('Alasan penolakan minimal 10 karakter!');
            return;
        }
        data.rejection_reason = reason;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    
    // Construct URL - try to get pengajuan ID safely
    let pengajuanId = '{{ isset($pengajuan->id) ? $pengajuan->id : "0" }}';
    let processUrl = `/pengajuan/${pengajuanId}/prodi/process`;
    
    fetch(processUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            alert(result.message || 'Pengajuan berhasil diproses');
            // Try to redirect to staff pengajuan index, fallback to reload
            try {
                window.location.href = '{{ route("staff.pengajuan.index") }}';
            } catch (e) {
                window.location.reload();
            }
        } else {
            alert('Error: ' + (result.message || 'Terjadi kesalahan'));
            // Reset button
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error processing pengajuan:', error);
        alert('Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.');
        // Reset button
        button.disabled = false;
        button.innerHTML = originalText;
    });
    
    // Close modals
    if (action === 'approve') closeApproveModal();
    if (action === 'reject') closeRejectModal();
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.id === 'approveModal') {
        closeApproveModal();
    }
    if (event.target.id === 'rejectModal') {
        closeRejectModal();
    }
});

// Handle Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeApproveModal();
        closeRejectModal();
    }
});
</script>