const express = require('express');
const multer = require('multer');
const fs = require('fs-extra');
const path = require('path');
const bodyParser = require('body-parser');
const open = require('open');

const app = express();
const PORT = 4020;

app.get('/', (req, res) => {
    res.redirect('/editor');
});

app.use(bodyParser.json());
app.use(express.static(path.join(__dirname, 'public')));

const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        const projectName = req.body.projectName || 'default';
        const type = file.fieldname === 'audio' ? 'music' : 'images';
        // Note: Assets are saved OUTSIDE the bundle in the real filesystem
        const dir = path.join(process.cwd(), 'public', 'assets', projectName, type);
        fs.ensureDirSync(dir);
        cb(null, dir);
    },
    filename: (req, file, cb) => {
        cb(null, Date.now() + '-' + file.originalname);
    }
});

const upload = multer({ storage });

app.get('/editor', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'editor.html'));
});

app.get('/presentation', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'presentation.html'));
});

app.get('/presentation.html', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'presentation.html'));
});

app.get('/api/projects', async (req, res) => {
    const assetsDir = path.join(process.cwd(), 'public', 'assets');
    await fs.ensureDir(assetsDir);
    const folders = await fs.readdir(assetsDir);
    const projectFolders = [];
    for (const f of folders) {
        if (fs.lstatSync(path.join(assetsDir, f)).isDirectory()) {
            projectFolders.push(f);
        }
    }
    res.json(projectFolders);
});

app.get('/api/all', async (req, res) => {
    const assetsDir = path.join(process.cwd(), 'public', 'assets');
    await fs.ensureDir(assetsDir);
    const folders = await fs.readdir(assetsDir);
    const projectsData = [];
    
    for (const f of folders) {
        const projectPath = path.join(assetsDir, f);
        if (fs.lstatSync(projectPath).isDirectory()) {
            const dataFile = path.join(projectPath, 'data.json');
            if (await fs.pathExists(dataFile)) {
                try {
                    const data = await fs.readJson(dataFile);
                    projectsData.push({
                        name: f,
                        data: data
                    });
                } catch (e) {
                    console.error(`Error reading project ${f}:`, e);
                }
            }
        }
    }
    res.json(projectsData);
});

app.get('/api/project/:name', async (req, res) => {
    console.log('API project called:', req.params.name);
    const filePath = path.join(process.cwd(), 'public', 'assets', req.params.name, 'data.json');
    if (await fs.pathExists(filePath)) {
        res.json(await fs.readJson(filePath));
    } else {
        res.status(404).json({ error: 'Project not found' });
    }
});

app.get('/api/:name', async (req, res) => {
    const filePath = path.join(process.cwd(), 'public', 'assets', req.params.name, 'data.json');
    if (await fs.pathExists(filePath)) {
        res.json(await fs.readJson(filePath));
    } else {
        res.status(404).json({ error: 'Project not found' });
    }
});

app.post('/api/save', async (req, res) => {
    const { projectName, data } = req.body;
    const projectDir = path.join(process.cwd(), 'public', 'assets', projectName);
    await fs.ensureDir(projectDir);
    await fs.writeJson(path.join(projectDir, 'data.json'), data, { spaces: 4 });
    res.json({ success: true });
});

app.post('/api/upload', upload.fields([{ name: 'image', maxCount: 1 }, { name: 'audio', maxCount: 1 }]), (req, res) => {
    const files = req.files;
    const response = {};
    if (files.image) response.imagePath = `/assets/${req.body.projectName}/images/${files.image[0].filename}`;
    if (files.audio) response.audioPath = `/assets/${req.body.projectName}/music/${files.audio[0].filename}`;
    res.json(response);
});

// Serve local assets as well (not just embedded ones)
const assetsPath = path.join(process.cwd(), 'public', 'assets');
fs.ensureDirSync(assetsPath);
app.use('/assets', express.static(assetsPath));

// Global Error Handler
app.use((err, req, res, next) => {
    console.error("Internal Server Error:", err);
    res.status(500).json({ error: 'Internal Server Error', details: err.message });
});

process.on('uncaughtException', (err) => {
    console.error('FATAL: Uncaught Exception:', err);
    // Keep console open for a bit if running in a window
    setTimeout(() => process.exit(1), 5000);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('Unhandled Rejection at:', promise, 'reason:', reason);
});

app.listen(PORT, async () => {
    console.log(`========================================`);
    console.log(`Server running at http://localhost:${PORT}`);
    console.log(`Base directory: ${__dirname}`);
    console.log(`Working directory: ${process.cwd()}`);
    console.log(`========================================`);

    try {
        await open(`http://localhost:${PORT}`);
    } catch (e) {
        console.error("Could not open browser automatically:", e.message);
    }
});
