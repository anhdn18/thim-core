const ghpages = require('gh-pages');

ghpages.publish('public-site', {
    repo: 'https://github.com/ThimPressWP/thim-core.git'
}, function () {
    console.log('done');
});