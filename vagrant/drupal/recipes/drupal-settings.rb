template "/home/vagrant/public_html/drupal/sites/default/settings.php" do
  source "settings.php.erb"
  variables({
     :database => 'drupal',
     :username => 'root',
     :password => '',
     :prefix => '',
     :host => 'localhost',
     :port => '3306'
  })
  action :create_if_missing
end

directory "/home/vagrant/public_html/drupal/sites/default/" do
  mode "0755"
end

directory "/home/vagrant/public_html/drupal/sites/default/files" do
  mode "0777"
  recursive true
end


%w[ files/config_vagrant files/config_vagrant/staging files/config_vagrant/active].each do |dir|
  directory "/home/vagrant/public_html/drupal/sites/default/#{dir}" do
    mode "0777"
  end
end

directory "/home/vagrant/public_html/drupal/sites/default/files/php" do
  action :delete
  recursive true
end
