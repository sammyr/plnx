<!-- Driver Buchung Card -->
<div class="info-card" style="
    background: linear-gradient(135deg, rgba(30, 30, 40, 0.95) 0%, rgba(20, 20, 30, 0.98) 100%);
    border-radius: 20px;
    padding: 32px;
    border: 1px solid rgba(212, 175, 55, 0.2);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
">
    <h2 style="
        color: #d4af37;
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 24px;
        letter-spacing: 0.5px;
    ">Driver jetzt buchen</h2>
    
    <!-- Preis Container -->
    <div style="
        background: rgba(212, 175, 55, 0.1);
        border: 2px solid rgba(212, 175, 55, 0.3);
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        margin-bottom: 24px;
    ">
        <div style="
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        ">Preis pro Minute</div>
        
        <div style="
            font-size: 56px;
            font-weight: 700;
            color: #d4af37;
            line-height: 1;
            margin-bottom: 8px;
        ">
            <span style="font-size: 32px; vertical-align: top;">€</span>49<sup style="font-size: 32px;">.99</sup>
        </div>
        
        <div style="
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        ">/Minute</div>
    </div>

    <!-- Stats Grid -->
    <div style="
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 24px;
    ">
        <div style="
            background: rgba(212, 175, 55, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        ">
            <div style="
                font-size: 32px;
                font-weight: 700;
                color: #d4af37;
                margin-bottom: 4px;
            ">2K</div>
            <div style="
                font-size: 12px;
                color: rgba(255, 255, 255, 0.6);
                text-transform: uppercase;
                letter-spacing: 1px;
            ">Qualität</div>
        </div>
        
        <div style="
            background: rgba(212, 175, 55, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        ">
            <div style="
                font-size: 20px;
                font-weight: 700;
                color: #d4af37;
                margin-bottom: 4px;
                line-height: 1.2;
            ">Echtzeit<br>Talk & Chat</div>
            <div style="
                font-size: 12px;
                color: rgba(255, 255, 255, 0.6);
                text-transform: uppercase;
                letter-spacing: 1px;
            ">Interaktion</div>
        </div>
    </div>

    <!-- Exklusiv Buchen Button -->
    <button onclick="openPaymentPopover('exclusive')" style="
        width: 100%;
        background: linear-gradient(135deg, #d4af37, #f4d03f);
        border: none;
        color: #0a0a0f;
        padding: 18px 32px;
        font-size: 16px;
        font-weight: 700;
        border-radius: 12px;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
        transition: all 0.3s;
        margin-bottom: 12px;
    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(212, 175, 55, 0.6)'" 
       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(212, 175, 55, 0.4)'">
        Exklusiv Buchen
    </button>

    <!-- Jetzt Buchen Button -->
    <button onclick="openPaymentPopover('standard')" style="
        width: 100%;
        background: transparent;
        border: 2px solid #d4af37;
        color: #d4af37;
        padding: 16px 32px;
        font-size: 16px;
        font-weight: 700;
        border-radius: 12px;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s;
    " onmouseover="this.style.background='rgba(212, 175, 55, 0.1)'; this.style.transform='translateY(-2px)'" 
       onmouseout="this.style.background='transparent'; this.style.transform='translateY(0)'">
        Jetzt Buchen
    </button>
</div>

<!-- Payment Popover -->
<div id="paymentPopover" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(10px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
" onclick="if(event.target === this) closePaymentPopover()">
    <div style="
        background: linear-gradient(135deg, rgba(30, 30, 40, 0.98) 0%, rgba(20, 20, 30, 1) 100%);
        border-radius: 24px;
        padding: 40px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        border: 1px solid rgba(212, 175, 55, 0.3);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
        position: relative;
    " onclick="event.stopPropagation()">
        <!-- Close Button -->
        <button onclick="closePaymentPopover()" style="
            position: absolute;
            top: 16px;
            right: 16px;
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            font-size: 28px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        " onmouseover="this.style.background='rgba(212, 175, 55, 0.1)'; this.style.color='#d4af37'" 
           onmouseout="this.style.background='none'; this.style.color='rgba(255, 255, 255, 0.6)'">×</button>

        <!-- Title -->
        <h2 style="
            color: #d4af37;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        " id="paymentTitle">Exklusiv Buchen</h2>
        
        <p style="
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            margin-bottom: 32px;
        ">Sichere Zahlung über verschlüsselte Verbindung</p>

        <!-- Payment Form -->
        <form onsubmit="processPayment(event)">
            <!-- Karteninhaber -->
            <div style="margin-bottom: 20px;">
                <label style="
                    display: block;
                    color: rgba(255, 255, 255, 0.8);
                    font-size: 13px;
                    font-weight: 600;
                    margin-bottom: 8px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                ">Karteninhaber</label>
                <input type="text" placeholder="Max Mustermann" value="Max Mustermann" required style="
                    width: 100%;
                    padding: 14px 16px;
                    background: rgba(255, 255, 255, 0.05);
                    border: 1px solid rgba(212, 175, 55, 0.2);
                    border-radius: 10px;
                    color: white;
                    font-size: 15px;
                    transition: all 0.3s;
                " onfocus="this.style.borderColor='#d4af37'; this.style.background='rgba(255, 255, 255, 0.08)'" 
                   onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
            </div>

            <!-- Kartennummer -->
            <div style="margin-bottom: 20px;">
                <label style="
                    display: block;
                    color: rgba(255, 255, 255, 0.8);
                    font-size: 13px;
                    font-weight: 600;
                    margin-bottom: 8px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                ">Kartennummer</label>
                <input type="text" placeholder="1234 5678 9012 3456" value="1234 5678 9012 3456" maxlength="19" required style="
                    width: 100%;
                    padding: 14px 16px;
                    background: rgba(255, 255, 255, 0.05);
                    border: 1px solid rgba(212, 175, 55, 0.2);
                    border-radius: 10px;
                    color: white;
                    font-size: 15px;
                    letter-spacing: 2px;
                    transition: all 0.3s;
                " onfocus="this.style.borderColor='#d4af37'; this.style.background='rgba(255, 255, 255, 0.08)'" 
                   onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
            </div>

            <!-- Ablaufdatum & CVV -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="
                        display: block;
                        color: rgba(255, 255, 255, 0.8);
                        font-size: 13px;
                        font-weight: 600;
                        margin-bottom: 8px;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                    ">Ablaufdatum</label>
                    <input type="text" placeholder="MM/JJ" value="12/29" maxlength="5" required style="
                        width: 100%;
                        padding: 14px 16px;
                        background: rgba(255, 255, 255, 0.05);
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        border-radius: 10px;
                        color: white;
                        font-size: 15px;
                        transition: all 0.3s;
                    " onfocus="this.style.borderColor='#d4af37'; this.style.background='rgba(255, 255, 255, 0.08)'" 
                       onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
                </div>
                <div>
                    <label style="
                        display: block;
                        color: rgba(255, 255, 255, 0.8);
                        font-size: 13px;
                        font-weight: 600;
                        margin-bottom: 8px;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                    ">CVV</label>
                    <input type="text" placeholder="123" value="123" maxlength="3" required style="
                        width: 100%;
                        padding: 14px 16px;
                        background: rgba(255, 255, 255, 0.05);
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        border-radius: 10px;
                        color: white;
                        font-size: 15px;
                        transition: all 0.3s;
                    " onfocus="this.style.borderColor='#d4af37'; this.style.background='rgba(255, 255, 255, 0.08)'" 
                       onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
                </div>
            </div>

            <!-- Kontaktinformationen -->
            <div style="margin-bottom: 20px;">
                <label style="
                    display: block;
                    color: rgba(255, 255, 255, 0.8);
                    font-size: 13px;
                    font-weight: 600;
                    margin-bottom: 8px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                ">E-Mail-Adresse</label>
                <input type="email" placeholder="max.mustermann@example.de" value="max.mustermann@example.de" required style="
                    width: 100%;
                    padding: 14px 16px;
                    background: rgba(255, 255, 255, 0.05);
                    border: 1px solid rgba(212, 175, 55, 0.2);
                    border-radius: 10px;
                    color: white;
                    font-size: 15px;
                    transition: all 0.3s;
                " onfocus="this.style.borderColor='#d4af37'; this.style.background='rgba(255, 255, 255, 0.08)'" 
                   onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="
                    display: block;
                    color: rgba(255, 255, 255, 0.8);
                    font-size: 13px;
                    font-weight: 600;
                    margin-bottom: 8px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                ">Rechnungsadresse</label>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <input type="text" placeholder="Vorname" value="Max" required style="
                        width: 100%;
                        padding: 14px 16px;
                        background: rgba(255, 255, 255, 0.05);
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        border-radius: 10px;
                        color: white;
                        font-size: 15px;
                        transition: all 0.3s;
                    " onfocus="this.style.borderColor='#d4af37'; this.style.background='rgba(255, 255, 255, 0.08)'" 
                       onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
                    <input type="text" placeholder="Nachname" value="Mustermann" required style="
                        width: 100%;
                        padding: 14px 16px;
                        background: rgba(255, 255, 255, 0.05);
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        border-radius: 10px;
                        color: white;
                        font-size: 15px;
                        transition: all 0.3s;
                    " onfocus="this.style.borderColor='#d4af37'; this.style.background='rgba(255, 255, 255, 0.08)'" 
                       onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
                </div>

                <div style="margin-bottom: 16px;">
                    <input type="text" placeholder="Firmenname (optional)" value="Musterfirma GmbH" style="
                        width: 100%;
                        padding: 14px 16px;
                        background: rgba(255, 255, 255, 0.05);
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        border-radius: 10px;
                        color: white;
                        font-size: 15px;
                        transition: all 0.3s;
                    " onfocus="this.style.borderColor='#d4af37'; this.style.background='rgba(255, 255, 255, 0.08)'" 
                       onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <input type="text" placeholder="Straße" value="Musterstraße" required style="
                        width: 100%;
                        padding: 14px 16px;
                        background: rgba(255, 255, 255, 0.05);
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        border-radius: 10px;
                        color: white;
                        font-size: 15px;
                        transition: all 0.3s;
                    " onfocus="this.style.borderColor='#d4af37'; this.style.background='rgba(255, 255, 255, 0.08)'" 
                       onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
                    <input type="text" placeholder="Hausnr." value="12" required style="
                        width: 100%;
                        padding: 14px 16px;
                        background: rgba(255, 255, 255, 0.05);
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        border-radius: 10px;
                        color: white;
                        font-size: 15px;
                        transition: all 0.3s;
                    " onfocus="this.style.borderColor='#d4af37'; this.style.background='rgba(255, 255, 255, 0.08)'" 
                       onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px;">
                    <input type="text" placeholder="PLZ" value="10115" maxlength="5" pattern="\d{5}" required style="
                        width: 100%;
                        padding: 14px 16px;
                        background: rgba(255, 255, 255, 0.05);
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        border-radius: 10px;
                        color: white;
                        font-size: 15px;
                        transition: all 0.3s;
                    " onfocus="this.style.borderColor='#d4af37'; this.style.background='rgba(255, 255, 255, 0.08)'" 
                       onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
                    <input type="text" placeholder="Ort" value="Berlin" required style="
                        width: 100%;
                        padding: 14px 16px;
                        background: rgba(255, 255, 255, 0.05);
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        border-radius: 10px;
                        color: white;
                        font-size: 15px;
                        transition: all 0.3s;
                    " onfocus="this.style.borderColor='#d4af37'; this.style.background='rgba(255, 255, 255, 0.08)'" 
                       onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
                </div>
            </div>

            <!-- Credits Auswahl -->
            <div style="margin-bottom: 24px;">
                <label style="
                    display: block;
                    color: rgba(255, 255, 255, 0.8);
                    font-size: 13px;
                    font-weight: 600;
                    margin-bottom: 12px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                ">Credits auswählen</label>
                <div style="display: grid; gap: 12px;">
                    <label data-package="5" style="
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        padding: 14px 16px;
                        background: rgba(255, 255, 255, 0.02);
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        border-radius: 12px;
                        cursor: pointer;
                        transition: all 0.3s;
                    ">
                        <input type="radio" name="creditPackage" value="5" style="accent-color: #d4af37; width: 18px; height: 18px; margin: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div style="color: rgba(255, 255, 255, 0.9); font-weight: 600;">5 Minuten</div>
                            <div class="package-price" style="color: rgba(255, 255, 255, 0.6); font-size: 13px;">Preis wird berechnet...</div>
                        </div>
                    </label>
                    <label data-package="10" style="
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        padding: 14px 16px;
                        background: rgba(255, 255, 255, 0.02);
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        border-radius: 12px;
                        cursor: pointer;
                        transition: all 0.3s;
                    ">
                        <input type="radio" name="creditPackage" value="10" style="accent-color: #d4af37; width: 18px; height: 18px; margin: 0;" checked>
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div style="color: rgba(255, 255, 255, 0.9); font-weight: 600;">10 Minuten</div>
                            <div class="package-price" style="color: rgba(255, 255, 255, 0.6); font-size: 13px;">Preis wird berechnet...</div>
                        </div>
                    </label>
                    <label data-package="20" style="
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        padding: 14px 16px;
                        background: rgba(255, 255, 255, 0.02);
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        border-radius: 12px;
                        cursor: pointer;
                        transition: all 0.3s;
                    ">
                        <input type="radio" name="creditPackage" value="20" style="accent-color: #d4af37; width: 18px; height: 18px; margin: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div style="color: rgba(255, 255, 255, 0.9); font-weight: 600;">20 Minuten</div>
                            <div class="package-price" style="color: rgba(255, 255, 255, 0.6); font-size: 13px;">Preis wird berechnet...</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Preis Info -->
            <div style="
                background: rgba(212, 175, 55, 0.1);
                border: 1px solid rgba(212, 175, 55, 0.3);
                border-radius: 12px;
                padding: 16px;
                margin-bottom: 24px;
                text-align: center;
            ">
                <div id="paymentInfoLabel" style="color: rgba(255, 255, 255, 0.6); font-size: 12px; margin-bottom: 4px;">Ausgewähltes Paket</div>
                <div style="color: #d4af37; font-size: 32px; font-weight: 700;" id="paymentAmount">€0.00</div>
                <div id="paymentHint" style="color: rgba(255, 255, 255, 0.5); font-size: 12px; margin-top: 6px;">Bitte ein Minutenpaket auswählen.</div>
            </div>

            <!-- Rechtliche Einwilligungen -->
            <div style="margin-bottom: 12px; display: flex; gap: 12px; align-items: flex-start;">
                <input type="checkbox" id="privacyCheck" required style="width: 18px; height: 18px; margin-top: 4px; accent-color: #d4af37;">
                <label for="privacyCheck" style="color: rgba(255, 255, 255, 0.6); font-size: 13px; line-height: 1.6;">
                    Ich habe die <a href="#privacy" style="color: #d4af37; text-decoration: none;">Datenschutzerklärung</a> gelesen und akzeptiere die Verarbeitung meiner Daten.
                </label>
            </div>

            <div style="margin-bottom: 24px; display: flex; gap: 12px; align-items: flex-start;">
                <input type="checkbox" id="termsCheck" required style="width: 18px; height: 18px; margin-top: 4px; accent-color: #d4af37;">
                <label for="termsCheck" style="color: rgba(255, 255, 255, 0.6); font-size: 13px; line-height: 1.6;">
                    Ich bestätige, die <a href="#terms" style="color: #d4af37; text-decoration: none;">AGB</a> sowie die <a href="#revocation" style="color: #d4af37; text-decoration: none;">Widerrufsbelehrung</a> erhalten zu haben und stimme der sofortigen Leistungserbringung zu.
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" style="
                width: 100%;
                background: linear-gradient(135deg, #d4af37, #f4d03f);
                border: none;
                color: #0a0a0f;
                padding: 18px 32px;
                font-size: 16px;
                font-weight: 700;
                border-radius: 12px;
                cursor: pointer;
                text-transform: uppercase;
                letter-spacing: 1px;
                box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
                transition: all 0.3s;
            " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(212, 175, 55, 0.6)'" 
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(212, 175, 55, 0.4)'">
                Zahlung abschließen
            </button>
        </form>

        <!-- Security Info -->
        <div style="
            text-align: center;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.4);
            font-size: 12px;
        ">
            🔒 Sichere SSL-Verschlüsselung
        </div>
    </div>
</div>

<script>
    const PACKAGE_PRICING = {
        exclusive: {
            minutePrice: 99.99,
            packages: {
                5: 469.95,
                10: 899.90,
                20: 1699.80
            }
        },
        standard: {
            minutePrice: 49.99,
            packages: {
                5: 239.95,
                10: 449.90,
                20: 799.80
            }
        }
    };

    function formatEuro(amount) {
        return amount.toLocaleString('de-DE', { style: 'currency', currency: 'EUR' });
    }

    function updatePackageDisplay(type) {
        const config = PACKAGE_PRICING[type] || PACKAGE_PRICING.standard;
        document.querySelectorAll('#paymentPopover label[data-package]').forEach(label => {
            const minutes = parseInt(label.getAttribute('data-package'), 10);
            const priceElement = label.querySelector('.package-price');
            const price = config.packages[minutes];
            priceElement.textContent = `${formatEuro(price)} • ${minutes} Minuten`; 

            label.onmouseover = () => label.style.borderColor = '#d4af37';
            label.onmouseout = () => {
                if (!label.querySelector('input').checked) {
                    label.style.borderColor = 'rgba(212, 175, 55, 0.2)';
                }
            };

            label.querySelector('input').addEventListener('change', () => {
                document.querySelectorAll('#paymentPopover label[data-package]').forEach(otherLabel => {
                    otherLabel.style.borderColor = otherLabel.querySelector('input').checked ? '#d4af37' : 'rgba(212, 175, 55, 0.2)';
                    otherLabel.style.background = otherLabel.querySelector('input').checked ? 'rgba(212, 175, 55, 0.1)' : 'rgba(255, 255, 255, 0.02)';
                });
                const selectedPrice = config.packages[minutes];
                const amount = document.getElementById('paymentAmount');
                const hint = document.getElementById('paymentHint');
                amount.textContent = formatEuro(selectedPrice);
                hint.textContent = `${minutes} Minuten Paket • ${formatEuro(config.minutePrice)} pro Minute`;
            });
        });

        // Set initial selection
        const checkedInput = document.querySelector('input[name="creditPackage"]:checked');
        if (checkedInput) {
            checkedInput.dispatchEvent(new Event('change'));
        }
    }

    function openPaymentPopover(type) {
        const popover = document.getElementById('paymentPopover');
        const title = document.getElementById('paymentTitle');
        const amount = document.getElementById('paymentAmount');
        const infoLabel = document.getElementById('paymentInfoLabel');
        const hint = document.getElementById('paymentHint');

        const priceConfig = {
            exclusive: {
                title: 'Exklusiv Buchen',
                info: 'Wähle ein Minutenpaket für deine Premium-Session mit priorisiertem Driver.'
            },
            standard: {
                title: 'Jetzt Buchen',
                info: 'Wähle ein Minutenpaket für deine Standardsession.'
            }
        };

        const config = priceConfig[type] || priceConfig.standard;
        title.textContent = config.title;
        infoLabel.textContent = 'Ausgewähltes Paket';
        amount.textContent = '€0.00';
        hint.textContent = config.info;

        updatePackageDisplay(type);

        popover.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closePaymentPopover() {
        const popover = document.getElementById('paymentPopover');
        popover.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function processPayment(event) {
        event.preventDefault();
        // Simuliere Zahlung
        const submitBtn = event.target.querySelector('button[type="submit"]');
        submitBtn.textContent = 'Verarbeite...';
        submitBtn.disabled = true;
        
        setTimeout(() => {
            closePaymentPopover();
            submitBtn.textContent = 'Zahlung abschließen';
            submitBtn.disabled = false;
            
            // Hole roomId aus URL
            const urlParams = new URLSearchParams(window.location.search);
            const roomId = urlParams.get('room');
            
            // Zeige Buchungsbestätigung und öffne Chat ZUERST
            console.log('[Payment] Prüfe window.openDriverChat:', typeof window.openDriverChat);
            if (typeof window.openDriverChat === 'function') {
                console.log('[Payment] Rufe window.openDriverChat() auf...');
                window.openDriverChat();
                console.log('[Payment] window.openDriverChat() aufgerufen');
            } else {
                console.error('[Payment] window.openDriverChat ist KEINE Funktion!', window.openDriverChat);
            }
            
            // Dann sende Reservierung an Server (für alle Besucher sichtbar)
            if (roomId) {
                fetch(`http://localhost:3000/api/reserve/${encodeURIComponent(roomId)}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => {
                    console.log('[Payment] Server Response Status:', res.status);
                    if (!res.ok) throw new Error(`Server Error: ${res.status}`);
                    return res.json();
                })
                .then(data => {
                    console.log(`[Payment] Stream ${roomId} auf Server reserviert:`, data);
                })
                .catch(err => {
                    console.error('[Payment] Reservierung fehlgeschlagen:', err);
                    console.error('[Payment] Fehler-Details:', err.message);
                });
            }
        }, 2000);
    }

    // ESC-Taste zum Schließen
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closePaymentPopover();
        }
    });
</script>
