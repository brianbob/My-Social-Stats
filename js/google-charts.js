(function ($, Drupal, drupalSettings) {
  $(document).ready(function() {
    // Load the Visualization API and the corechart package.
    google.charts.load('current', {'packages':['corechart']});
    // Check to see if the posts chart is on the page. If it is, draw it.
    if ($('#facebook_posts_chart_div').length) {
      google.charts.setOnLoadCallback(drawFacebookPostsChart);
    }
    // Check to see if the shares chart is on the page. If it is, draw it.
    if ($('#facebook_shares_chart_div').length) {
      google.charts.setOnLoadCallback(drawFacebookSharesChart);
    }

  });

  // Callback that creates and populates a data table,
  // instantiates the bar chart, passes in the data and
  // draws it.
  function drawFacebookPostsChart() {

    // Get the data from Dupal and create the data table.
    var data_object = drupalSettings.facebook_block.facebook_posts;
    var data_array = Object.entries(data_object);
    var data = google.visualization.arrayToDataTable(data_array);

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



  // Callback that creates and populates a data table,
  // instantiates the pie chart, passes in the data and
  // draws it.
  function drawFacebookSharesChart() {

    // Get the data from Dupal and create the data table.
    var data_object = drupalSettings.facebook_block.facebook_shares;
    var data_array = Object.entries(data_object);
    var data = google.visualization.arrayToDataTable(data_array);

    // Set chart options
    var options = {
      'height': 300,
      'titlePosition': 'none',
      //'legend': 'none',
      'backgroundColor': 'transparent',
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
      },
      'pieHole': 0.4,
      'pieSliceBorderColor': "transparent"
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.PieChart(document.getElementById('facebook_shares_chart_div'));
    chart.draw(data, options);
  }
}(jQuery, Drupal, drupalSettings));
