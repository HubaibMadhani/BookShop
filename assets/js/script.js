document.addEventListener('DOMContentLoaded', ()=>{ const cartCount = document.getElementById('cart-count'); if(cartCount){ /* placeholder for future dynamic updates */ } });

// Contact form behavior
document.addEventListener('DOMContentLoaded', ()=>{
	const form = document.getElementById('contact-form');
	if(!form) return;
	const alertEl = document.getElementById('contact-alert');

	const showAlert = (type, text)=>{
		alertEl.className = 'alert ' + (type === 'success' ? 'success' : 'error');
		alertEl.textContent = text;
		alertEl.style.display = 'block';
	};

	const clearErrors = ()=>{
		form.querySelectorAll('.form-error').forEach(e=>{e.textContent='';e.style.display='none'});
	};

	const setFieldError = (name, text)=>{
		const el = form.querySelector('.form-error[data-for="'+name+'"]');
		if(el){ el.textContent = text; el.style.display = 'block'; }
	};

	form.addEventListener('submit', async (e)=>{
		e.preventDefault();
		clearErrors();
		if(alertEl) alertEl.style.display='none';
		const name = form.elements['name'].value.trim();
		const email = form.elements['email'].value.trim();
		const message = form.elements['message'].value.trim();
		let hasError=false;
		if(name.length<2){ setFieldError('name','Please enter your name'); hasError=true }
		if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){ setFieldError('email','Please enter a valid email'); hasError=true }
		if(message.length<10){ setFieldError('message','Message must be at least 10 characters'); hasError=true }
		if(hasError) return;

		const data = new FormData(form);

		const submitBtn = form.querySelector('button[type=submit]');
		submitBtn.disabled = true;
		submitBtn.textContent = 'Sending...';

		try{
			const resp = await fetch(window.location.href, { method: 'POST', body: data, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
			const json = await resp.json();
			if(json.success){
				showAlert('success', json.message || 'Thanks — we will get back to you soon.');
				form.reset();
			} else {
				if(json.errors && json.errors.length) json.errors.forEach(err=> showAlert('error', err));
				else showAlert('error','Unable to send message. Please try again later.');
			}
		}catch(err){
			showAlert('error','Network error. Please try again.');
		}finally{
			submitBtn.disabled=false; submitBtn.textContent='Send message';
		}
	});

	document.getElementById('contact-reset')?.addEventListener('click', ()=>{ form.reset(); clearErrors(); if(alertEl) alertEl.style.display='none' });
});

// Registration form client-side validation
document.addEventListener('DOMContentLoaded', ()=>{
	const form = document.getElementById('register-form');
	if(!form) return;
	const setError = (el, msg) => {
		let f = el.parentElement.querySelector('.form-error');
		if(!f){ f = document.createElement('div'); f.className = 'form-error'; el.parentElement.appendChild(f); }
		f.textContent = msg; f.style.display = 'block';
	}
	const clearAll = ()=> form.querySelectorAll('.form-error').forEach(e=>{e.textContent=''; e.style.display='none'});
	form.addEventListener('submit', (e)=>{
		clearAll();
		const name = form.elements['name'].value.trim();
		const email = form.elements['email'].value.trim();
		const password = form.elements['password'].value;
		const password_confirm = form.elements['password_confirm'].value;
		let ok = true;
		if(name.length < 2){ setError(form.elements['name'], 'Enter your name (2+ chars)'); ok = false; }
		if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){ setError(form.elements['email'], 'Enter a valid email'); ok = false; }
		if(password.length < 8){ setError(form.elements['password'], 'Password must be at least 8 characters'); ok = false; }
		if(password !== password_confirm){ setError(form.elements['password_confirm'], 'Passwords do not match'); ok = false; }
		if(!ok) e.preventDefault();
	});
});

// Admin dashboard message modal behavior
document.addEventListener('DOMContentLoaded', ()=>{
	const backdrop = document.getElementById('message-modal-backdrop');
	if(!backdrop) return;
	const modalName = document.getElementById('modal-name');
	const modalEmail = document.getElementById('modal-email');
	const modalDate = document.getElementById('modal-date');
	const modalMessage = document.getElementById('modal-message');
	const modalClose = document.getElementById('modal-close');
	const modalToggle = document.getElementById('modal-toggle-read');
	const modalDelete = document.getElementById('modal-delete');

	let currentId = null;

	document.querySelectorAll('button[data-action="view-message"]').forEach(btn=>{
		btn.addEventListener('click', (e)=>{
			const tr = e.target.closest('tr');
			currentId = tr.dataset.id;
			modalName.textContent = tr.dataset.name;
			modalEmail.textContent = tr.dataset.email;
			modalDate.textContent = tr.dataset.date;
			modalMessage.textContent = tr.dataset.message;
			modalToggle.textContent = tr.dataset.read === '1' ? 'Mark unread' : 'Mark read';
			backdrop.style.display = 'flex';
		});
	});

	modalClose.addEventListener('click', ()=>{ backdrop.style.display = 'none'; currentId=null });

	modalToggle.addEventListener('click', async ()=>{
		if(!currentId) return;
		const read = modalToggle.textContent.includes('unread') ? 'mark_unread' : 'mark_read';
		try{
			const res = await fetch('messages.php', { method: 'POST', headers: { 'X-Requested-With':'XMLHttpRequest', 'Content-Type':'application/x-www-form-urlencoded' }, body: 'id='+encodeURIComponent(currentId)+'&action='+encodeURIComponent(read) });
			const json = await res.json();
			if(json.success){
				// update row
				const tr = document.querySelector('tr[data-id="'+currentId+'"]');
				if(tr){ tr.dataset.read = (read==='mark_read') ? '1' : '0'; tr.querySelectorAll('td')[4].textContent = (read==='mark_read') ? 'Yes' : 'No'; }
				modalToggle.textContent = (read==='mark_read') ? 'Mark unread' : 'Mark read';
				// update unread counter
				const unreadEl = document.getElementById('unread-count');
				if(unreadEl){
					let val = parseInt(unreadEl.textContent || '0', 10);
					if(read==='mark_read') val = Math.max(0, val-1);
					else val = val + 1;
					unreadEl.textContent = val;
				}
			}
		}catch(err){ console.error(err); }
	});

	modalDelete.addEventListener('click', async ()=>{
		if(!currentId) return; if(!confirm('Delete this message?')) return;
		try{
			const res = await fetch('messages.php', { method: 'POST', headers: { 'X-Requested-With':'XMLHttpRequest', 'Content-Type':'application/x-www-form-urlencoded' }, body: 'id='+encodeURIComponent(currentId)+'&action=delete' });
			const json = await res.json();
			if(json.success){
				const tr = document.querySelector('tr[data-id="'+currentId+'"]'); if(tr) tr.remove();
				backdrop.style.display = 'none'; currentId=null;
			}
		}catch(err){ console.error(err); }
	});

	// Dashboard search, filter and bulk actions
	const searchInput = document.getElementById('msg-search');
	const filterUnread = document.getElementById('filter-unread');
	const selectAllBtn = document.getElementById('select-all');
	const markReadBtn = document.getElementById('mark-read');
	const markUnreadBtn = document.getElementById('mark-unread');
	const deleteBtn = document.getElementById('delete-selected');
	const exportBtn = document.getElementById('export-selected');

	// Show/hide delete button depending on selection
	const updateBulkVisibility = ()=>{
		const ids = getSelectedIds();
		if(deleteBtn) deleteBtn.style.display = ids.length ? '' : 'none';
		// export button will remain visible (user requested), but if you prefer hide when no selection uncomment next line
		// if(exportBtn) exportBtn.style.display = ids.length ? '' : 'none';
	};

	const rows = () => Array.from(document.querySelectorAll('#messages-table tbody tr'));
	const getSelectedIds = () => Array.from(document.querySelectorAll('.select-row:checked')).map(i=>i.value);

	const applyFilters = ()=>{
		const q = (searchInput?.value||'').toLowerCase();
		const unreadOnly = !!filterUnread?.checked;
		rows().forEach(tr=>{
			const text = (tr.dataset.name+' '+tr.dataset.email+' '+tr.dataset.message+' '+tr.dataset.date).toLowerCase();
			const matches = !q || text.indexOf(q) !== -1;
			const unreadMatch = !unreadOnly || tr.dataset.read === '0' || tr.dataset.read === 'false' || tr.dataset.read === '';
			tr.style.display = (matches && unreadMatch) ? '' : 'none';
		});
	};

	searchInput?.addEventListener('input', applyFilters);
	filterUnread?.addEventListener('change', applyFilters);

	selectAllBtn?.addEventListener('click', ()=>{
    const all = Array.from(document.querySelectorAll('#messages-table tbody tr:not([style*="display: none"]) .select-row'));
    const anyUnchecked = all.some(cb => !cb.checked);
    all.forEach(cb=>cb.checked = anyUnchecked);
		updateBulkVisibility();
  });

	// Update visibility when individual checkboxes change
	document.querySelectorAll('.select-row').forEach(cb=> cb.addEventListener('change', updateBulkVisibility));
	// initial state
	updateBulkVisibility();

	const postBulk = async (action, ids)=>{
		if(!ids.length) return alert('No messages selected');
		const form = new URLSearchParams();
		form.append('action', action);
		ids.forEach(id=> form.append('ids[]', id));
		try{
			const res = await fetch('messages.php', { method:'POST', headers: { 'X-Requested-With':'XMLHttpRequest', 'Content-Type':'application/x-www-form-urlencoded' }, body: form.toString() });
			const json = await res.json();
			if(json.success) return true;
		}catch(err){ console.error(err); }
		alert('Operation failed');
		return false;
	};

	markReadBtn?.addEventListener('click', async ()=>{
		const ids = getSelectedIds(); if(!ids.length) return alert('No messages selected');
    const before = ids.filter(id=>{ const tr=document.querySelector('tr[data-id="'+id+'"]'); return tr && tr.dataset.read !== '1'; });
    if(await postBulk('mark_read', ids)){
      ids.forEach(id=>{ const tr = document.querySelector('tr[data-id="'+id+'"]'); if(tr){ tr.dataset.read='1'; tr.querySelector('.read-status').textContent = 'Yes'; } });
      const unreadEl = document.getElementById('unread-count'); if(unreadEl){ let val = parseInt(unreadEl.textContent||'0',10); val = Math.max(0, val - before.length); unreadEl.textContent = val; }
    }
  });
  markUnreadBtn?.addEventListener('click', async ()=>{
    const ids = getSelectedIds(); if(!ids.length) return alert('No messages selected');
    const before = ids.filter(id=>{ const tr=document.querySelector('tr[data-id="'+id+'"]'); return tr && tr.dataset.read === '1'; });
    if(await postBulk('mark_unread', ids)){
      ids.forEach(id=>{ const tr = document.querySelector('tr[data-id="'+id+'"]'); if(tr){ tr.dataset.read='0'; tr.querySelector('.read-status').textContent = 'No'; } });
      const unreadEl = document.getElementById('unread-count'); if(unreadEl){ let val = parseInt(unreadEl.textContent||'0',10); val = val + before.length; unreadEl.textContent = val; }
    }
  });

  deleteBtn?.addEventListener('click', async ()=>{
    const ids = getSelectedIds(); if(!ids.length) return alert('No messages selected');
    if(!confirm('Delete selected messages?')) return;
    const beforeUnread = ids.filter(id=>{ const tr = document.querySelector('tr[data-id="'+id+'"]'); return tr && (tr.dataset.read === '0' || tr.dataset.read === 'false' || tr.dataset.read === ''); });
    if(await postBulk('delete', ids)){
      ids.forEach(id=>{ const tr = document.querySelector('tr[data-id="'+id+'"]'); if(tr) tr.remove(); });
      const unreadEl = document.getElementById('unread-count'); if(unreadEl){ let val = parseInt(unreadEl.textContent||'0',10); val = Math.max(0, val - beforeUnread.length); unreadEl.textContent = val; }
    }
  });

  exportBtn?.addEventListener('click', (e)=>{
    const ids = getSelectedIds();
    if(ids.length){ e.preventDefault(); window.location = 'messages_export.php?ids='+ids.join(','); }
  });

	// Handle per-row delete buttons (messages page)
	document.querySelectorAll('button[data-action="delete-row"]').forEach(btn=>{
		btn.addEventListener('click', async (e)=>{
			const id = btn.dataset.id || btn.closest('tr')?.dataset.id;
			if(!id) return;
			if(!confirm('Delete this message?')) return;
			try{
				const res = await fetch('messages.php', { method:'POST', headers: { 'X-Requested-With':'XMLHttpRequest', 'Content-Type':'application/x-www-form-urlencoded' }, body: 'id='+encodeURIComponent(id)+'&action=delete' });
				const json = await res.json();
				if(json.success){ const tr = document.querySelector('tr[data-id="'+id+'"]'); if(tr) tr.remove(); }
				else alert('Delete failed');
			}catch(err){ console.error(err); alert('Delete failed'); }
		});

		// Main-admin only: reset user password via prompt
		document.querySelectorAll('button[data-action="reset-password"]').forEach(btn=>{
			btn.addEventListener('click', async (e)=>{
				const id = btn.dataset.id;
				if(!id) return;
				const pw = prompt('Enter new password (min 8 chars):');
				if(!pw) return;
				if(pw.length < 8){ alert('Password must be at least 8 characters'); return; }
				try{
					const body = 'action=reset_password&id='+encodeURIComponent(id)+'&new_password='+encodeURIComponent(pw);
					const res = await fetch('users.php', { method:'POST', headers:{ 'X-Requested-With':'XMLHttpRequest', 'Content-Type':'application/x-www-form-urlencoded' }, body });
					// server redirects for non-AJAX; try to parse JSON if available
					try{ const json = await res.json(); if(json && json.success){ alert('Password reset'); } else { location.reload(); } }catch(e){ location.reload(); }
				}catch(err){ console.error(err); alert('Failed to reset password'); }
			});
		});
	});