<a href="viewer.php" style="text-decoration: none; display: inline-block; cursor: pointer;">
    <div class="plnx-logo" style="
        font-family: 'Impact', 'Arial Black', sans-serif; 
        font-weight: 900; 
        letter-spacing: 10px; 
        font-size: 78px; 
        background: linear-gradient(145deg, 
            #9b7ec4 0%, 
            #b8a3d1 15%, 
            #d4af37 35%, 
            #f4d03f 50%, 
            #d4af37 65%, 
            #b8a3d1 85%, 
            #9b7ec4 100%
        );
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        background-size: 200% 200%;
        animation: gradientShift 3s ease infinite;
        filter: 
            drop-shadow(0 2px 1px rgba(0, 0, 0, 0.8))
            drop-shadow(0 4px 3px rgba(212, 175, 55, 0.4))
            drop-shadow(0 8px 8px rgba(184, 163, 209, 0.3))
            drop-shadow(0 0 20px rgba(212, 175, 55, 0.2));
        margin: 0;
        padding: 20px 0;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    " onmouseover="
        this.style.filter='drop-shadow(0 3px 2px rgba(0, 0, 0, 0.9)) drop-shadow(0 6px 6px rgba(212, 175, 55, 0.6)) drop-shadow(0 12px 12px rgba(184, 163, 209, 0.5)) drop-shadow(0 0 30px rgba(212, 175, 55, 0.4))'; 
        this.style.transform='scale(1.08) translateY(-2px)';
        this.style.letterSpacing='12px'
    " onmouseout="
        this.style.filter='drop-shadow(0 2px 1px rgba(0, 0, 0, 0.8)) drop-shadow(0 4px 3px rgba(212, 175, 55, 0.4)) drop-shadow(0 8px 8px rgba(184, 163, 209, 0.3)) drop-shadow(0 0 20px rgba(212, 175, 55, 0.2))'; 
        this.style.transform='scale(1) translateY(0)';
        this.style.letterSpacing='10px'
    ">PLNX</div>
</a>

<style>
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    /* Mobile Anpassungen */
    @media (max-width: 768px) {
        .plnx-logo {
            font-size: 48px !important;
            letter-spacing: 6px !important;
            padding: 12px 0 !important;
        }
        
        .plnx-logo:hover {
            letter-spacing: 8px !important;
        }
    }
    
    @media (max-width: 480px) {
        .plnx-logo {
            font-size: 36px !important;
            letter-spacing: 4px !important;
            padding: 10px 0 !important;
        }
        
        .plnx-logo:hover {
            letter-spacing: 6px !important;
            transform: scale(1.05) translateY(-1px) !important;
        }
    }
</style>
