(function ($, Drupal, drupalSettings) {
  $(document).ready(function() {
    // Load the Visualization API and the corechart package.
    google.charts.load('current', {'packages':['corechart']});
    // Set a callback to run when the Google Visualization API is loaded.
    google.charts.setOnLoadCallback(drawFacebookPostsChart);

  });

  // Callback that creates and populates a data table,
  // instantiates the pie chart, passes in the data and
  // draws it.
  function drawFacebookPostsChart() {

    // Get the data from Dupal and create the data table.
    var data_object = drupalSettings.facebook_block.facebook_posts;
    var data_array = Object.entries(data_object);
    var data = google.visualization.arrayToDataTable(data_array);

    var test_data = [
        ['City', '2010 Population', '2000 Population'],
        ['New York City, NY', 8175000, 8008000],
        ['Los Angeles, CA', 3792000, 3694000],
        ['Chicago, IL', 2695000, 2896000],
        ['Houston, TX', 2099000, 1953000],
        ['Philadelphia, PA', 1526000, 1517000]
      ];
    console.dir(data_object);
    console.dir(test_data);
    console.dir(data_array);

    // Set chart options
    var options = {
      'height': 300,
      'titlePosition': 'none',
      'seriesType': 'bars',
      'legend': 'none',
      'backgroundColor': 'transparent',
      'is3D': true,
      'fontName': 'Raleway',
      'allowHtml': true,
      'hAxis': {
        'textStyle' : {
          'color': '#bfc9d3'
        }
      },
      'vAxis': {
        'textStyle' : {
          'color': '#bfc9d3'
        }
      }
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.BarChart(document.getElementById('facebook_posts_chart_div'));
    chart.draw(data, options);
  }
}(jQuery, Drupal, drupalSettings));
