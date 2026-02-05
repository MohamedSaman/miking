    <div class="login-container">
        <style>
            .login-form-overlay { 
                background: rgba(0, 0, 0, 0.25); 
                padding: 35px; 
                border-radius: 20px; 
                backdrop-filter: blur(6px); 
                box-shadow: 0 20px 50px rgba(0,0,0,0.3);
                border: 1px solid rgba(255,255,255,0.15);
            }
            .separator-line { height:1px; background:rgba(255,255,255,0.1); margin:20px 0; border-radius:2px; }
            .connect-section { text-align:center; padding-top: 5px; }
            .connect-title { color:rgba(255,255,255,0.8); font-size: 0.9rem; font-weight:500; margin-bottom:15px; }
            .connect-links { display:flex; gap:15px; justify-content:center; align-items:center; }
            .connect-icon { width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; color:#fff; font-size:18px; transition: all 0.3s ease; text-decoration:none; }
            .connect-icon:hover { transform: scale(1.1); filter: brightness(1.1); }
            .connect-icon.email { background:#e84b3c; box-shadow:0 4px 12px rgba(232, 75, 60, 0.3); }
            .connect-icon.whatsapp { background:#25d366; box-shadow:0 4px 12px rgba(37, 211, 102, 0.3); }
            .form-options .form-check-label { color: rgba(255,255,255,0.9) !important; font-size: 0.85rem; }
            .forgot-link { color: rgba(255,255,255,0.9) !important; text-decoration: none; font-size: 0.85rem; }
            .forgot-link:hover { color: #fff !important; text-decoration: underline; }
            .form-control { background: rgba(255,255,255,1); border: none; height: 50px; }
            .form-control:focus { box-shadow: 0 0 0 3px rgba(44, 74, 147, 0.3); }
            .developed-by {
                position: absolute;
                bottom: 20px;
                width: 100%;
                text-align: center;
                color: rgba(255, 255, 255, 0.6);
                font-size: 0.8rem;
                font-weight: 500;
                letter-spacing: 1px;
                z-index: 2;
                pointer-events: none;
            }
            @media (max-width:420px){ .login-form-overlay { padding: 25px; } }
        </style>
        <!-- Full-screen background image -->
        <div class="background-image"></div>

        <!-- Centered login form overlay -->
        <div class="login-form-overlay">
            <!-- User icon -->
            <div class="user-icon-container">
                <i class="bi bi-person-circle"></i>
            </div>

            <form wire:submit.prevent="login">
          

                <!-- Email field -->
                <div class="form-group">
                    <input type="email"
                        class="form-control {{ $errors->has('email') ? 'is-invalid shake' : '' }}"
                        wire:model="email"
                        placeholder="Enter Email"
                        required
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                    @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password field -->
                <div class="form-group">
                    <input type="password"
                        class="form-control {{ $errors->has('password') ? 'is-invalid shake' : '' }}"
                        wire:model="password"
                        placeholder="Enter Password"
                        required
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                    @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember & Forgot options -->
                <div class="d-flex justify-content-between form-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" wire:model="remember" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-link">Forgot Password</a>
                </div>

                <!-- Login button -->
                <button type="submit" class="btn btn-primary login-btn">Login</button>

                <!-- Separator line -->
                <div class="separator-line"></div>

                <!-- Connect with us section -->
                <div class="connect-section">
                    <p class="connect-title">Connect with us</p>
                    <div class="connect-links">
                        <a href="mailto:contact@webxkey.com" class="connect-icon email" title="Email us">
                            <i class="bi bi-envelope-fill"></i>
                        </a>
                        <a href="https://api.whatsapp.com/send/?phone=94755299721&text=Hi%21+I%27m+interested+in+your+services.&type=phone_number&app_absent=0" 
                           target="_blank" 
                           class="connect-icon whatsapp" 
                           title="WhatsApp us" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Developed by Webxkey text -->
        <div class="developed-by">
            Developed by Webxkey
        </div>
    </div>
