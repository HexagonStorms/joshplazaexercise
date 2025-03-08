// api endpoint
const API_ENDPOINT = 'scraper.php'

// function to generate random colors! pretty every time you refresh :)
function generateRandomColors(count) {
    const colors = []
    for (let i = 0; i < count; i++) {
        const r = Math.floor(Math.random() * 200)
        const g = Math.floor(Math.random() * 200)
        const b = Math.floor(Math.random() * 200)
        colors.push(`rgba(${r}, ${g}, ${b}, 0.8)`)
    }
    return colors
}

// Pie chart for sources
function createSourcesChart(sourceCounts) {
    const ctx = document.getElementById('sourcesChart').getContext('2d')
    const sources = Object.keys(sourceCounts)
    const counts = Object.values(sourceCounts)
    
    const backgroundColors = generateRandomColors(sources.length)
    
    // create chart
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: sources,
            datasets: [{
                label: 'Article Count',
                data: counts,
                backgroundColor: backgroundColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                },
                title: {
                    display: true,
                    text: 'Distribution of News Sources'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || ''
                            const value = context.raw || 0
                            const total = context.dataset.data.reduce((a, b) => a + b, 0)
                            const percentage = Math.round((value / total) * 100)
                            return `${label}: ${value} (${percentage}%)`
                        }
                    }
                }
            }
        }
    })
}

// Populate news table data
function populateNewsTable(news) {
    // get table body
    const tableBody = document.getElementById('newsTableBody')
    tableBody.innerHTML = ''
    
    // loop through each news
    news.forEach(item => {
        // create table row
        const row = document.createElement('tr')
        
        // Headline
        const headlineCell = document.createElement('td')
        headlineCell.textContent = item.headline
        row.appendChild(headlineCell)
        
        // Source
        const sourceCell = document.createElement('td')
        sourceCell.textContent = item.publication
        row.appendChild(sourceCell)

        // Author
        const authorCell = document.createElement('td')
        authorCell.textContent = item.author
        row.appendChild(authorCell)
        
        // Summary
        const summaryCell = document.createElement('td')
        summaryCell.textContent = item.summary
        row.appendChild(summaryCell)
        
        // append row to table body
        tableBody.appendChild(row)
    })
}

function createKeywordsChart(keywordCounts) {
    const ctx = document.getElementById('keywordsChart').getContext('2d')
    const keywords = Object.keys(keywordCounts)
    const counts = Object.values(keywordCounts)
    
    // using a blue for this chart
    const backgroundColors = keywords.map((_, i) => {
        const intensity = 100 + Math.floor((i / keywords.length) * 155)
        return `rgba(25, ${intensity}, 220, 0.8)`
    })
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: keywords,
            datasets: [{
                label: 'Frequency',
                data: counts,
                backgroundColor: backgroundColors,
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Top Keywords in Articles'
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Frequency'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Keywords'
                    }
                }
            }
        }
    })
}

function fetchAndDisplayData() {
    // show loading
    document.getElementById('sources-loading').classList.remove('d-none')
    document.getElementById('news-loading').classList.remove('d-none')
    
    // hide error
    document.getElementById('sources-error').classList.add('d-none')
    document.getElementById('news-error').classList.add('d-none')
    
    // fetch data from api
    fetch(API_ENDPOINT)
        .then(response => {
            // check if response is ok
            if (!response.ok) {
                throw new Error('Failed to fetch data')
            }
            return response.json()
        })
        .then(data => {
            // hide loading
            document.getElementById('sources-loading').classList.add('d-none')
            document.getElementById('news-loading').classList.add('d-none')
            document.getElementById('keywords-loading').classList.add('d-none')
            
            // debug sutff
            console.log('API Response:', data)
            console.log('Source Counts:', data.sourceCounts)
            
            if (!data.sourceCounts || Object.keys(data.sourceCounts).length === 0) {
                throw new Error('No source count data available')
            }
            
            // create charts
            createSourcesChart(data.sourceCounts)
            populateNewsTable(data.news)
            createKeywordsChart(data.keywords)
        })
        .catch(error => {
            // hide loading
            document.getElementById('sources-loading').classList.add('d-none')
            document.getElementById('news-loading').classList.add('d-none')
            
            // display errors
            const sourcesError = document.getElementById('sources-error')
            sourcesError.textContent = `Error: ${error.message}`
            sourcesError.classList.remove('d-none')
            
            const newsError = document.getElementById('news-error')
            newsError.textContent = `Error: ${error.message}`
            newsError.classList.remove('d-none')
            
            // log error
            console.error('Error fetching data:', error)
        })
}

// executes when dom is loaded
document.addEventListener('DOMContentLoaded', function() {
    fetchAndDisplayData()
})