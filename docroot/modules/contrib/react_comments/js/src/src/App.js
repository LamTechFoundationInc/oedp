import React, { Component } from 'react';
import './App.css';
import CommentBox from './components/CommentBox';
import Comment from './components/Comment';
import api from './utils/api';
import findCommentByIdRecursive from './utils/findCommentByIdRecursive';
import Throbber from 'react-throbber';
import Constants from './utils/constants';

class App extends Component {
  state = {
    currentUser: {
      hasPermission: () => false
    },
    comments: null,
    loading: true,
    openMenu: null
  };

  postComment = (commentText, anonName) => {
    return api.postComment(commentText, anonName).then((response) => {
      if (response.code === 'success') {
        const newState = { ...this.state };
        newState.comments.unshift(response.data);
        this.setState(newState);
        return response;
      }
      else {
        return response;
      }
    });
  };

  postReply = (commentId, commentText, anonName) => {
    return api.postReply(commentId, commentText, anonName).then((response) => {
      if (response.code === 'success') {
        const newCommentState = [ ...this.state.comments ];
        const commentRepliedTo = findCommentByIdRecursive(commentId, newCommentState);

        if (commentRepliedTo.replies) {
          commentRepliedTo.replies.push(response.data);
        }
        else {
          commentRepliedTo.replies = [response.data];
        }

        this.setState({ comments: newCommentState });
        return response;
      }
      else {
        return response;
      }
    });
  };

  flagComment = (commentId) => {
    api.flagComment(commentId).then(() => {
      const newCommentState = [ ...this.state.comments ];
      const comment = findCommentByIdRecursive(commentId, newCommentState);
      comment.status = Constants.flagged;
      this.setState({ comments: newCommentState });
    }).catch((err) => {
      alert(err.message);
    });
  };

  publishComment = (commentId) => {
    api.publishComment(commentId).then(() => {
      const newCommentState = [ ...this.state.comments ];
      const comment = findCommentByIdRecursive(commentId, newCommentState);
      comment.status = Constants.published;
      comment.published = Constants.published;
      this.setState({ comments: newCommentState });
    }).catch((err) => {
      alert(err.message);
    });
  };

  unpublishComment = (commentId) => {
    api.unpublishComment(commentId).then(() => {
      const newCommentState = [ ...this.state.comments ];
      const comment = findCommentByIdRecursive(commentId, newCommentState);
      comment.status = Constants.unpublished;
      comment.published = Constants.unpublished;
      this.setState({ comments: newCommentState });
    }).catch((err) => {
      alert(err.message);
    });
  };

  deleteComment = (commentId) => {
    if (window.confirm("Are you sure you want to delete this comment? This action cannot be undone.")) {
      return api.deleteComment(commentId).then(() => {
        const newCommentState = [...this.state.comments];
        const comment = findCommentByIdRecursive(commentId, newCommentState);
        comment.status = Constants.deleted;
        this.setState({comments: newCommentState});
      });
    }
    else {
      return Promise.resolve();
    }
  };

  saveEdit = (commentId, commentText) => {
    return api.saveEdit(commentId, commentText).then((response) => {
      if (response.code === 'success') {
        const newCommentState = [ ...this.state.comments ];
        const comment = findCommentByIdRecursive(commentId, newCommentState);
        comment.comment = commentText;
        this.setState({ comments: newCommentState });
      }
      else {
        return response;
      }
    });
  };

  toggleMenu = (id) => {
    if (this.state.openMenu === id) {
      this.setState({ openMenu: null });
    }
    else {
      this.setState({ openMenu: id });
    }
  };

  componentWillMount() {
    if (this.state.comments !== null) return;

    const nid = document.getElementById(api.getAppContainerId()).getAttribute('data-entity-id');
    const origin = document.getElementById(api.getAppContainerId()).getAttribute('data-origin');
    window.commentsAppNid = nid;
    window.commentsApiBaseUrl = origin;

    api.getComments()
      .then((response) => {
        this.setState({
          comments: response.data.data || [],
          loading: false
        });
      })
      .catch((err) => {
        console.error(err);
        this.setState({
          error: true,
          loading: false
        });
      });

    api.getMe()
      .then((response) => {
        const currentUser = response.data.data ? response.data.data.current_user : {};
        currentUser.hasPermission = function (permission) {
          return this.permissions && Object.keys(this.permissions).map((i) => this.permissions[i]).includes(permission);
        };
        this.setState({
          currentUser: currentUser
        });
      })
      .catch((err) => {
        console.error(err);
        this.setState({
          error: true,
          loading: false
        });
      });
  }

  render() {
    if (this.state.loading) {
      return <Throbber size="35px"/>
    }

    if (this.state.error) {
      return <div style={{display: 'none'}}>unable to load comments</div>
    }

    return (
      <div className="rc_react-comments">
        <CommentBox
          user={this.state.currentUser}
          type="comment"
          postComment={this.postComment}
        />

        { this.state.comments.map((el, i) => { return (
              <Comment
                key={i}
                level={0}
                currentUser={this.state.currentUser}
                postReply={this.postReply}
                saveEdit={this.saveEdit}
                vote={this.vote}
                openMenu={this.state.openMenu}
                toggleMenu={this.toggleMenu}
                flagComment={this.flagComment}
                publishComment={this.publishComment}
                unpublishComment={this.unpublishComment}
                deleteComment={this.deleteComment}
                {...el}
              />
            )
          }) }
      </div>
    );
  }
}

export default App;
