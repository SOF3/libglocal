// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.psi.impl;

import java.util.List;
import org.jetbrains.annotations.*;
import com.intellij.lang.ASTNode;
import com.intellij.psi.PsiElement;
import com.intellij.psi.PsiElementVisitor;
import com.intellij.psi.util.PsiTreeUtil;
import static io.github.sof3.libglocal.intellij.parser.LgcElements.*;
import com.intellij.extapi.psi.ASTWrapperPsiElement;
import io.github.sof3.libglocal.intellij.psi.*;

public class LgcMessageGroupImpl extends ASTWrapperPsiElement implements LgcMessageGroup {

  public LgcMessageGroupImpl(@NotNull ASTNode node) {
    super(node);
  }

  public void accept(@NotNull LgcVisitor visitor) {
    visitor.visitMessageGroup(this);
  }

  public void accept(@NotNull PsiElementVisitor visitor) {
    if (visitor instanceof LgcVisitor) accept((LgcVisitor)visitor);
    else super.accept(visitor);
  }

  @Override
  @NotNull
  public LgcEnd getEnd() {
    return findNotNullChildByClass(LgcEnd.class);
  }

  @Override
  @Nullable
  public LgcMessageBlock getMessageBlock() {
    return findChildByClass(LgcMessageBlock.class);
  }

  @Override
  @Nullable
  public LgcMessageGroup getMessageGroup() {
    return findChildByClass(LgcMessageGroup.class);
  }

  @Override
  @Nullable
  public PsiElement getEquals() {
    return findChildByType(EQUALS);
  }

  @Override
  @Nullable
  public PsiElement getIndentDecrease() {
    return findChildByType(INDENT_DECREASE);
  }

  @Override
  @Nullable
  public PsiElement getIndentIncrease() {
    return findChildByType(INDENT_INCREASE);
  }

  @Override
  @NotNull
  public PsiElement getGroupId() {
    return findNotNullChildByType(IDENTIFIER);
  }

}
