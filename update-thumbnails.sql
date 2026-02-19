-- =========================================
-- UPDATE THUMBNAIL GAME PAKAI URL INTERNET
-- Jalankan di phpMyAdmin > tab SQL
-- =========================================

-- Mobile Legends
UPDATE games SET 
    thumbnail = 'https://play-lh.googleusercontent.com/EOVSBqt4pJfcZZBhyStEaS5oXMbmKVJelqhMiQiohBEMpVJIZObXW8cXJrJkFPi5CA=w240-h480-rw',
    banner = 'https://play-lh.googleusercontent.com/EOVSBqt4pJfcZZBhyStEaS5oXMbmKVJelqhMiQiohBEMpVJIZObXW8cXJrJkFPi5CA=w240-h480-rw'
WHERE slug = 'mobile-legends';

-- Free Fire
UPDATE games SET 
    thumbnail = 'https://play-lh.googleusercontent.com/WWcssdzTZvx7jcBBReRTHwFmVuRKzMFtHsVEDJGWbKOyDNFGPp4XkhOCwrWEMBF3oUE=w240-h480-rw',
    banner = 'https://play-lh.googleusercontent.com/WWcssdzTZvx7jcBBReRTHwFmVuRKzMFtHsVEDJGWbKOyDNFGPp4XkhOCwrWEMBF3oUE=w240-h480-rw'
WHERE slug = 'free-fire';

-- PUBG Mobile  
UPDATE games SET 
    thumbnail = 'https://play-lh.googleusercontent.com/JRd05pyBH41qjgsJuWduRJpDeZG0Hnb0yjf2DqB7vc7TdE1LgA7MH9F8XZLYM0T-A=w240-h480-rw',
    banner = 'https://play-lh.googleusercontent.com/JRd05pyBH41qjgsJuWduRJpDeZG0Hnb0yjf2DqB7vc7TdE1LgA7MH9F8XZLYM0T-A=w240-h480-rw'
WHERE slug = 'pubg-mobile';

-- Genshin Impact
UPDATE games SET 
    thumbnail = 'https://play-lh.googleusercontent.com/So4HqRMEHGgbjvPPT9AQjzSlAqPiXLvNBSjb3mZjp1JGNbxS1NeSvOdAEbgJSUTYtg=w240-h480-rw',
    banner = 'https://play-lh.googleusercontent.com/So4HqRMEHGgbjvPPT9AQjzSlAqPiXLvNBSjb3mZjp1JGNbxS1NeSvOdAEbgJSUTYtg=w240-h480-rw'
WHERE slug = 'genshin-impact';

-- Valorant
UPDATE games SET 
    thumbnail = 'https://play-lh.googleusercontent.com/0Kma6DxRJhSP5dCd7PFiCy-nt_c2YUHH1bJVR1mfXlvIZyJ2Gg52SKKVb58b1D_fMU=w240-h480-rw',
    banner = 'https://play-lh.googleusercontent.com/0Kma6DxRJhSP5dCd7PFiCy-nt_c2YUHH1bJVR1mfXlvIZyJ2Gg52SKKVb58b1D_fMU=w240-h480-rw'
WHERE slug = 'valorant';

-- Cek hasilnya
SELECT id, name, slug, thumbnail FROM games;
