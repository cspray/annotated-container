# Contributing to Annotated Container

Hi! Thanks for taking the time to consider contributing to Annotated Container. I greatly appreciate any meaningful documentation or code contributions. The rest of this document talks about how you can best ensure your contributions land in `main`!

## Create an Issue First

For changes to the codebase you should generally create an Issue first. The API is stabilized at this point and maintaining backwards compatability is important. Additionally, I am hesitant to add any new Attributes to the codebase as each different Attribute has a significant amount of work and overhead attached to it. If the feature can be achieved with the existing Attributes that should be preferred.

Exceptions to this guideline are if the changes fix a bug, is strictly documentation, or is a security problem. For security related problems, please review the [SECURITY.md](./SECURITY.md) file.

## Tests, Oh Glorious Tests!

This codebase is well tested. While we don't necessarily strictly enforce 100% code coverage that is the goal we're striving towards. If you fix a bug, implement a new feature, or otherwise add logical lines of code you should also add the appropriate testing.

## Review Project Roadmap

Beyond reviewing the documentation found in-repo I also recommend that you check out the [Annotated Container Project and Roadmap](https://github.com/users/cspray/projects/1). The future of Annotated Container has been fairly well planned out. If you'd like to contribute but aren't sure where to get started reviewing and picking up one of the issues in the Roadmap would be fantastic!
