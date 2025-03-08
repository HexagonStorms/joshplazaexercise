// api endpoint
const API_ENDPOINT = 'scraper.php'

// function to generate random colors
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

// function to create sources pie chart
function createSourcesChart(sourceCounts) {
    // get canvas element
    const ctx = document.getElementById('sourcesChart').getContext('2d')
    
    // get sources and counts
    const sources = Object.keys(sourceCounts)
    const counts = Object.values(sourceCounts)
    
    // generate random colors
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

// function to populate news table
function populateNewsTable(news) {
    // get table body
    const tableBody = document.getElementById('newsTableBody')
    
    // clear table body
    tableBody.innerHTML = ''
    
    // loop through each news
    news.forEach(item => {
        // create table row
        const row = document.createElement('tr')
        
        // create headline cell
        const headlineCell = document.createElement('td')
        headlineCell.textContent = item.headline
        row.appendChild(headlineCell)
        
        // create source cell
        const sourceCell = document.createElement('td')
        sourceCell.textContent = item.publication
        row.appendChild(sourceCell)

        // create author cell
        const authorCell = document.createElement('td')
        authorCell.textContent = item.author
        row.appendChild(authorCell)
        
        // create summary cell
        const summaryCell = document.createElement('td')
        summaryCell.textContent = item.summary
        row.appendChild(summaryCell)
        
        // append row to table body
        tableBody.appendChild(row)
    })
}

// function to fetch and display data
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
            
            // debug data
            console.log('API Response:', data)
            console.log('Source Counts:', data.source_counts)
            
            // check if source_counts exists and has data
            if (!data.source_counts || Object.keys(data.source_counts).length === 0) {
                throw new Error('No source count data available')
            }
            
            // create sources chart
            createSourcesChart(data.source_counts)
            
            // populate news table
            populateNewsTable(data.news)
        })
        .catch(error => {
            // hide loading
            document.getElementById('sources-loading').classList.add('d-none')
            document.getElementById('news-loading').classList.add('d-none')
            
            // show error
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

// execute when dom is loaded
document.addEventListener('DOMContentLoaded', function() {
    // fetch and display data
    fetchAndDisplayData()
})