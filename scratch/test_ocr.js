function parseOCRText(text) {
    const lines = text.split('\n').map(line => line.trim()).filter(line => line.length > 0);
    
    let ic = '';
    let phone = '';
    let name = '';
    let village = '';
    let gender = '';

    const icRegex = /\b(\d{6})[- ]?(\d{2})[- ]?(\d{4})\b/;
    const phoneRegex = /\b(01[0-9])[- ]?([0-9]{7,8})\b/;

    // 1. Try to find IC
    const icMatch = text.match(icRegex);
    if (icMatch) {
        ic = `${icMatch[1]}-${icMatch[2]}-${icMatch[3]}`;
        const lastDigit = parseInt(icMatch[3].slice(-1));
        gender = (lastDigit % 2 === 0) ? 'Wanita' : 'Lelaki';
    }

    // 2. Try to find Phone
    const phoneMatch = text.match(phoneRegex);
    if (phoneMatch) {
        phone = `${phoneMatch[1]}-${phoneMatch[2]}`;
    }

    // 3. Robust Name Parsing
    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        const upperLine = line.toUpperCase();
        
        if (/NAMA|NAME/i.test(upperLine)) {
            let potentialName = line;
            potentialName = potentialName.replace(/NAMA PENUH/i, '');
            potentialName = potentialName.replace(/NAMA/i, '');
            potentialName = potentialName.replace(/NAME/i, '');
            potentialName = potentialName.replace(/^[^a-zA-Z0-9]*/, '');
            potentialName = potentialName.replace(/^[:\-=\s]*/, '');
            potentialName = potentialName.trim();
            
            if (potentialName.length >= 3) {
                name = potentialName;
                break;
            } else {
                if (i + 1 < lines.length) {
                    const nextLine = lines[i+1].trim();
                    if (nextLine.length >= 2 && !/^(?:IC|NO|TEL|PHONE|JANTINA|GENDER|KAMPUNG|KAWASAN|ALAMAT|VILLAGE|ADDRESS|STATUS)[:\-=\s]*$/i.test(nextLine)) {
                        name = nextLine;
                        break;
                    }
                }
            }
        }
    }

    // 4. Robust Kampung / Kawasan Parsing
    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        const upperLine = line.toUpperCase();
        
        if (/KAWASAN|KAMPUNG|ALAMAT|VILLAGE|ADDRESS/i.test(upperLine)) {
            let potentialVillage = line;
            potentialVillage = potentialVillage.replace(/KAWASAN/i, '');
            potentialVillage = potentialVillage.replace(/KAMPUNG/i, '');
            potentialVillage = potentialVillage.replace(/ALAMAT/i, '');
            potentialVillage = potentialVillage.replace(/VILLAGE/i, '');
            potentialVillage = potentialVillage.replace(/ADDRESS/i, '');
            potentialVillage = potentialVillage.replace(/^[^a-zA-Z0-9]*/, '');
            potentialVillage = potentialVillage.replace(/^[:\-=\s]*/, '');
            potentialVillage = potentialVillage.trim();
            
            if (potentialVillage.length >= 3) {
                village = potentialVillage;
                break;
            } else {
                if (i + 1 < lines.length) {
                    const nextLine = lines[i+1].trim();
                    if (nextLine.length >= 2 && !/^(?:IC|NO|TEL|PHONE|JANTINA|GENDER|NAMA|NAME|STATUS|KAMPUNG|KAWASAN|ALAMAT|VILLAGE|ADDRESS)[:\-=\s]*$/i.test(nextLine)) {
                        village = nextLine;
                        break;
                    }
                }
            }
        }
    }

    if (name) {
        name = name.toUpperCase().replace(/[^A-Z\s@']/g, '').trim();
    }
    if (village) {
        village = village.toUpperCase().trim();
    }

    return { ic, phone, name, village, gender };
}

// Test cases
const cases = [
    {
        name: "Single Line Village",
        text: `NAMA PENUH: AHMAD BIN ABDULLAH\nNO KAD PENGENALAN: 900101-13-5555\nNO TELEFON: 012-3456789\nKAMPUNG / KAWASAN: KAMPUNG DATA KAKUS`
    },
    {
        name: "Multi Line Village",
        text: `NAMA PENUH: AHMAD BIN ABDULLAH\nNO KAD PENGENALAN: 900101-13-5555\nNO TELEFON: 012-3456789\nKAMPUNG / KAWASAN:\nKAMPUNG DATA KAKUS`
    },
    {
        name: "Separated Label Village",
        text: `NAMA PENUH:\nAHMAD BIN ABDULLAH\nNO KAD PENGENALAN: 900101-13-5555\nNO TELEFON: 012-3456789\nKAMPUNG /\nKAWASAN:\nKAMPUNG DATA KAKUS`
    },
    {
        name: "Separated Label Village 2",
        text: `NAMA PENUH:\nAHMAD BIN ABDULLAH\nNO KAD PENGENALAN: 900101-13-5555\nNO TELEFON: 012-3456789\nKAMPUNG / KAWASAN\nTAMAN SRI AMAN`
    }
];

cases.forEach(c => {
    console.log(`--- Test Case: ${c.name} ---`);
    console.log(parseOCRText(c.text));
});
