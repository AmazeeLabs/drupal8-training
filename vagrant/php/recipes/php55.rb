# install python-software properties to enable add-apt-repo
package "python-software-properties"

# Add Ondrej's php5 repository
execute "Add Ondrej's php5.5 repository" do
  command "add-apt-repository ppa:ondrej/php5"
  ignore_failure true
end

execute "apt-get update" do
  command "apt-get update"
  ignore_failure true
  action :run
end

package 'php5'
package 'php5-cli'
