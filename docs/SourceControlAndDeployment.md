## GotChosen Website Source Control and Deployment

The website uses Git for source control and all development funnels up into the
"master" branch. The "master" branch should be the most stable branch in our
repository and we should be able to create a production build based off of it at
any given time.

The website uses Capistrano to perform deployments to designated servers
based on Git Branches. We currently utilize two separate environments for doing
Development Testing (Dev) and Integration Testing (Staging). These environments
mirror our Production environment in infrastructure and setup, they're just less
powerful machines.

### Git Policies

#### Branching and Merging Policy

Development should take place on non-master branches that are named after what
the task you're working on is. We use certain branch prefixes based on the type
of change that's being made:

* **feature** - This prefix is used for branches where large features are being
developed. For example: `feature/evogames` was the branch name for the
'Evolution Games' project.
* **update** - This prefix is used for smaller updates that don't add a lot of
new code or primarily consist of just static content or verbiage updates.
For example: `update/eg_rules` was the branch name for a recent update to the
text of the 'Evolution Games' Terms of Service and Rules.
* **bug** - This prefix is used specifically for branches that contain just
bugfixes. For example: `bug/profile_video` was the branch name for a recent
update that fixed an unhandled Profile exception related to users not having
a video submitted despite being entered into the Video Scholarship.

Please keep your branch names short but as descriptive as possible of the change
that you'll be making.

#### Pull Requests into Master

To get your code into the `master` branch, you will need to open a Pull Request
through GitHub. Prior to opening your Pull Request, please make sure that you
have fully merged the latest code from `master` down into your branch.

**Please do _NOT_ use a Pull Request to merge `master` changes down to your
branch. Use the `git merge` command like below:**

```
git checkout master
git pull
git checkout branchname
git merge master
```

Once your code is fully merged and any conflicts are resolved, you may open a
Pull Request on GitHub and have another member of the team review your code.

### Deploying to the Dev Environment

There is no dedicated branch assigned to the Dev environment. When you deploy to
this environment, you need to specify a branch that you will be deploying. To do
this, you will use the following command:

`cap -s branch="branchname" dev deploy`

### Deploying to the Staging Environment

The Staging environment deploys based on the current contents of the "master"
branch. You can deploy to the Staging environment with the following command:

`cap staging deploy`

### Deploying to the Production Environment

The Production environment deploys based on the current contents of the "master"
branch. You can deploy to the Production environment with the following command:

`cap prod deploy`
