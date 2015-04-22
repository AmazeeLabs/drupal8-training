bash "Create Database" do
  user "root" # see attributes section below
  code <<-EOH
    echo "create database IF NOT EXISTS drupal" | mysql
    EOH
end

bash "Import Database" do
  user "root" # see attributes section below
  code <<-EOH
    echo "source /home/vagrant/public_html/assets/vanilla-drupal8/drupal_20150422.sql" | mysql --database=drupal
    EOH
end
