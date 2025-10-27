// Build optimization utilities
import { createGzip, createBrotliCompress } from 'zlib';
import { createReadStream, createWriteStream, statSync } from 'fs';
import { join, extname } from 'path';
import { glob } from 'glob';

/**
 * Compress files using Gzip and Brotli
 * @param {string} distPath - Distribution directory path
 */
export async function compressAssets(distPath = 'dist') {
  const files = await glob('**/*.{js,css,html,svg}', { cwd: distPath });

  for (const file of files) {
    const filePath = join(distPath, file);
    const stats = statSync(filePath);

    // Only compress files larger than 1KB
    if (stats.size > 1024) {
      await Promise.all([
        compressFile(filePath, 'gzip'),
        compressFile(filePath, 'brotli')
      ]);
    }
  }
}

/**
 * Compress a single file
 * @param {string} filePath - Path to the file
 * @param {string} algorithm - Compression algorithm ('gzip' or 'brotli')
 */
function compressFile(filePath, algorithm) {
  return new Promise((resolve, reject) => {
    const input = createReadStream(filePath);
    const output = createWriteStream(`${filePath}.${algorithm === 'gzip' ? 'gz' : 'br'}`);

    const compress = algorithm === 'gzip'
      ? createGzip({ level: 9 })
      : createBrotliCompress({
          params: {
            [constants.BROTLI_PARAM_QUALITY]: 11,
            [constants.BROTLI_PARAM_SIZE_HINT]: statSync(filePath).size
          }
        });

    input
      .pipe(compress)
      .pipe(output)
      .on('finish', resolve)
      .on('error', reject);
  });
}

/**
 * Analyze bundle sizes and report
 * @param {string} distPath - Distribution directory path
 */
export async function analyzeBundleSize(distPath = 'dist') {
  const files = await glob('**/*.{js,css}', { cwd: distPath });
  const analysis = [];

  for (const file of files) {
    const filePath = join(distPath, file);
    const stats = statSync(filePath);
    const gzipPath = `${filePath}.gz`;
    const brotliPath = `${filePath}.br`;

    let gzipSize = 0;
    let brotliSize = 0;

    try {
      gzipSize = statSync(gzipPath).size;
    } catch (e) {
      // Gzip file doesn't exist
    }

    try {
      brotliSize = statSync(brotliPath).size;
    } catch (e) {
      // Brotli file doesn't exist
    }

    analysis.push({
      file,
      originalSize: stats.size,
      gzipSize,
      brotliSize,
      compressionRatio: gzipSize ? ((stats.size - gzipSize) / stats.size * 100).toFixed(2) : 0
    });
  }

  // Sort by original size (largest first)
  analysis.sort((a, b) => b.originalSize - a.originalSize);

  console.log('\n📊 Bundle Analysis Report\n');
  console.log('File'.padEnd(50) + 'Original'.padEnd(12) + 'Gzip'.padEnd(12) + 'Brotli'.padEnd(12) + 'Savings');
  console.log('-'.repeat(90));

  let totalOriginal = 0;
  let totalGzip = 0;
  let totalBrotli = 0;

  for (const item of analysis) {
    totalOriginal += item.originalSize;
    totalGzip += item.gzipSize;
    totalBrotli += item.brotliSize;

    console.log(
      item.file.substring(0, 48).padEnd(50) +
      formatBytes(item.originalSize).padEnd(12) +
      formatBytes(item.gzipSize).padEnd(12) +
      formatBytes(item.brotliSize).padEnd(12) +
      `${item.compressionRatio}%`
    );
  }

  console.log('-'.repeat(90));
  console.log(
    'TOTAL'.padEnd(50) +
    formatBytes(totalOriginal).padEnd(12) +
    formatBytes(totalGzip).padEnd(12) +
    formatBytes(totalBrotli).padEnd(12) +
    `${((totalOriginal - totalGzip) / totalOriginal * 100).toFixed(2)}%`
  );

  const recommendations = generateRecommendations(analysis);
  if (recommendations.length > 0) {
    console.log('\n💡 Optimization Recommendations:\n');
    recommendations.forEach((rec, index) => {
      console.log(`${index + 1}. ${rec}`);
    });
  }
}

/**
 * Generate optimization recommendations
 * @param {Array} analysis - Bundle analysis data
 * @returns {Array} Array of recommendation strings
 */
function generateRecommendations(analysis) {
  const recommendations = [];
  const largeFiles = analysis.filter(item => item.originalSize > 500 * 1024); // > 500KB
  const lowCompressionFiles = analysis.filter(item =>
    item.gzipSize > 0 && parseFloat(item.compressionRatio) < 60
  );

  if (largeFiles.length > 0) {
    recommendations.push(
      `Consider code splitting for large files: ${largeFiles.map(f => f.file).join(', ')}`
    );
  }

  if (lowCompressionFiles.length > 0) {
    recommendations.push(
      `Low compression ratio detected. Check for duplicate code or large libraries: ${lowCompressionFiles.map(f => f.file).join(', ')}`
    );
  }

  const vendorFiles = analysis.filter(item => item.file.includes('vendor'));
  const vendorSize = vendorFiles.reduce((sum, item) => sum + item.originalSize, 0);

  if (vendorSize > 1024 * 1024) { // > 1MB
    recommendations.push(
      'Consider splitting vendor bundles further or using dynamic imports for less critical libraries'
    );
  }

  return recommendations;
}

/**
 * Format bytes to human readable format
 * @param {number} bytes - Number of bytes
 * @returns {string} Formatted string
 */
function formatBytes(bytes) {
  if (bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Check for unused exports (basic implementation)
 * @param {string} srcPath - Source code path
 */
export async function checkUnusedExports(srcPath = 'src') {
  console.log('\n🔍 Checking for potentially unused exports...\n');

  // This is a simplified implementation
  // In a real scenario, you'd use tools like ts-unused-exports or depcheck
  const jsFiles = await glob('**/*.{js,vue,ts}', {
    cwd: srcPath,
    ignore: ['node_modules/**', 'dist/**', '**/*.test.js', '**/*.spec.js']
  });

  const exports = new Set();
  const imports = new Set();

  // Basic regex to find exports and imports (simplified)
  const exportRegex = /export\s+(?:default\s+)?(?:function|class|const|let|var)\s+(\w+)/g;
  const importRegex = /import\s+.*?[{,\s](\w+)[},\s].*?from/g;

  console.log('This is a basic check. Use dedicated tools like depcheck for comprehensive analysis.');
  console.log('Files analyzed:', jsFiles.length);
}

// Export utilities for use in build scripts
export default {
  compressAssets,
  analyzeBundleSize,
  checkUnusedExports,
  formatBytes
};