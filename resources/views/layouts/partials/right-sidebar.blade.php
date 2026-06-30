<!-- ─── SETTINGS PANEL ─── -->
<div id="settingsPanel">
    <div class="settings-header">
        <span class="fw-semibold">Theme Settings</span>
        <button onclick="document.getElementById('settingsPanel').classList.remove('open')" style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--bs-body-color)">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="settings-section">
        <h6>Color Scheme</h6>
        <div class="settings-option">
            <button class="settings-btn active" id="btnLight" onclick="setScheme('light',this)">
                <i class="bi bi-sun me-1"></i>Light
            </button>
            <button class="settings-btn" id="btnDark" onclick="setScheme('dark',this)">
                <i class="bi bi-moon me-1"></i>Dark
            </button>
        </div>
    </div>
    <div class="settings-section">
        <h6>Sidebar Size</h6>
        <div class="settings-option">
            <button class="settings-btn active" onclick="setSidebar('default',this)">Default</button>
            <button class="settings-btn" onclick="setSidebar('condensed',this)">Condensed</button>
            <button class="settings-btn" onclick="setSidebar('hidden',this)">Hidden</button>
        </div>
    </div>
</div>

<span class="settings-trigger" onclick="openSettings()">
    <i class="bi bi-gear"></i>
</span>