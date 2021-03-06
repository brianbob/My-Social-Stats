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
    // Check to see if the likes vs reactons chart is on the page. If it is, draw it.
    if ($('#facebook_likes_chart_div').length) {
      google.charts.setOnLoadCallback(drawFacebookLikesChart);
    }
    // Check to see if the comments chart is on the page. If it is, draw it.
    if ($('#facebook_comments_chart_div').length) {
      google.charts.setOnLoadCallback(drawFacebookCommentChart);
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
      'height': 400,
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
    console.log(data_array);
    var data = google.visualization.arrayToDataTable(data_array);

    // Set chart options
    var options = {
      'height': 400,
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


  // Callback that creates and populates a data table,
  // instantiates the lines chart, passes in the data and
  // draws it.
  function drawFacebookLikesChart() {
    // Get the data from Dupal and create the data table.
    var data_object = drupalSettings.facebook_block.facebook_likes;
    var data_array = Object.entries(data_object);
    // This chart is a little more picky about how we format the data, so we need to format it
    // in a way google charts will accept.
    var formatted_array = [['Month', 'Likes', 'Reactions']];
    data_array.forEach(function(element) {
      formatted_array.push([element[0], element[1]['likes'], element[1]['reactions']]);
    });
    // Convert the properly-formatted data into a data table.
    var data = google.visualization.arrayToDataTable(formatted_array);
    // Set chart options
    var options = {
      'height': 400,
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
    var chart = new google.visualization.LineChart(document.getElementById('facebook_likes_chart_div'));
    chart.draw(data, options);
  }

  // Callback that creates and populates a data table,
  // instantiates the area chart, passes in the data and
  // draws it.
  function drawFacebookCommentChart() {

    // Get the data from Dupal and create the data table.
    var data_object = drupalSettings.facebook_block.facebook_comments;
    var data_array = Object.entries(data_object);
    console.log(data_array);
    var data = google.visualization.arrayToDataTable(data_array);

    // Set chart options
    var options = {
      'height': 400,
      'titlePosition': 'none',
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
    var chart = new google.visualization.AreaChart(document.getElementById('facebook_comments_chart_div'));
    chart.draw(data, options);
  }
}(jQuery, Drupal, drupalSettings));
